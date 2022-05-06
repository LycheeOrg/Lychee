<?php

namespace App\Actions\Photo\Strategies;

use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Exceptions\ModelDBException;
use App\Image\FlysystemFile;
use App\Image\ImageHandlerInterface;
use App\Image\NativeLocalFile;
use App\Image\StreamStat;
use App\ModelFunctions\MOVFormat;
use App\Models\Photo;
use App\Models\SizeVariant;
use FFMpeg\FFMpeg;
use Illuminate\Contracts\Container\BindingResolutionException;

class AddStandaloneStrategy extends AddBaseStrategy
{
	protected ImageHandlerInterface $sourceImage;
	protected NativeLocalFile $sourceFile;

	/**
	 * @throws FrameworkException
	 */
	public function __construct(AddStrategyParameters $parameters, NativeLocalFile $sourceFile)
	{
		try {
			$newPhoto = new Photo();
			// We already set the timestamps (`created_at`, `updated_at`) on
			// initialization time, not save time.
			// This keeps the creation timestamps ordered as the images are
			// uploaded/imported.
			// This should be the most consistent/expected behaviour.
			// Otherwise, the creation time would reflect the point of time when
			// Lychee has finished processing the image (rotated, cropped,
			// generated thumbnails).
			// This might lead to "race conditions", i.e. some images might
			// outpace each other.
			// This would not lead to data loss or worse, but images might
			// appear in a different order than users expect.
			$newPhoto->updateTimestamps();
			parent::__construct($parameters, $newPhoto);
			$this->sourceImage = resolve(ImageHandlerInterface::class);
			$this->sourceFile = $sourceFile;
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}

	/**
	 * @return Photo
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 * @throws MediaFileUnsupportedException
	 */
	public function do(): Photo
	{
		// Create and save "bare" photo object without size variants
		$this->hydrateMetadata();
		$this->photo->is_public = $this->parameters->is_public;
		$this->photo->is_starred = $this->parameters->is_starred;
		$this->setParentAndOwnership();

		// Unfortunately, we must read the entire file once to create the
		// true, original checksum.
		// It does **not** suffice to use the stream statistics, when the
		// image file is loaded, because we cannot guarantee that the image
		// loader reads the file entirely in one pass.
		//  a) The image loader may decide to seek in the file, skip
		//     certain parts (like EXIF information), re-read chunks of the
		//     file multiple times or out-of-order.
		//  b) The image loader may not load the entire file, if the image
		//     stream is shorter than the file and followed by additional
		//     non-image information (e.g. as it is the case for Google
		//     Live Photos)
		$this->photo->original_checksum = StreamStat::createFromLocalFile($this->sourceFile)->checksum;

		try {
			// Load source image
			$this->sourceImage->load($this->sourceFile);

			/** @var SizeVariantNamingStrategy $namingStrategy */
			$namingStrategy = resolve(SizeVariantNamingStrategy::class);
			$namingStrategy->setFallbackExtension(
				$this->sourceFile->getOriginalExtension()
			);

			// Create target file and symlink/copy/move source file to
			// target.
			// If import strategy request to delete the source file.
			// `$this->sourceFile` will be deleted after this step.
			// But `$this->sourceImage` remains in memory.
			$targetFile = $namingStrategy->createFile(SizeVariant::ORIGINAL);
			$streamStat = $this->putSourceIntoFinalDestination($targetFile);

			// The original and final checksum may differ, if the photo has
			// been rotated by `putSourceIntoFinalDestination` while being
			// moved into final position.
			// If the photo has been rotated, the checksum may have changed.
			$this->photo->checksum = $streamStat->checksum;
			$this->photo->save();

			// Create original size variant of photo
			$this->photo->size_variants->create(
				SizeVariant::ORIGINAL,
				$targetFile->getRelativePath(),
				$this->sourceImage->getDimensions(),
				$streamStat->bytes
			);
		} catch (\Exception $e) {
			// If source file could not be put into final destination, remove
			// freshly created photo from DB to avoid having "zombie" entries.
			try {
				$this->photo->delete();
			} catch (\Throwable) {
				// Sic! If anything goes wrong here, we still throw the original exception
			}
			throw $e;
		}

		// Create remaining size variants
		try {
			/** @var SizeVariantFactory $sizeVariantFactory */
			$sizeVariantFactory = resolve(SizeVariantFactory::class);
			$sizeVariantFactory->init($this->photo, $namingStrategy, $this->sourceImage);
			$sizeVariantFactory->createSizeVariants();
		} catch (\Throwable $t) {
			// Don't re-throw the exception, because we do not want the
			// import to fail completely only due to missing size variants.
			// There are just too many options why the creation of size
			// variants may fail: the user has uploaded an unsupported file
			// format, GD and Imagick are both not available or disabled
			// by configuration, etc.
			report($t);
		}

		$this->handleGoogleMotionPicture();

		return $this->photo;
	}

	/**
	 * Moves/copies/symlinks source file to final destination and
	 * normalizes orientation, if necessary.
	 *
	 * Note, {@link AddStandaloneStrategy::$sourceFile} and
	 * {@link AddStandaloneStrategy::$sourceImage} must be set before this
	 * method is called.
	 *
	 * If import via symbolic link is requested, then a symbolic link
	 * from `$targetFile` to {@link AddStandaloneStrategy::$sourceFile} is
	 * created.
	 * Otherwise the content of {@link AddStandaloneStrategy::$sourceFile}
	 * is physically copied/moved into `$targetFile`.
	 *
	 * If the source file requires normalization, then
	 * {@link AddStandaloneStrategy::$sourceImage} is saved to `$targetFile`.
	 * This step implicitly corrects the orientation.
	 * Otherwise, the original byte stream from
	 * {@link AddStandaloneStrategy::$sourceFile} is written to `$targetFile`
	 * without modifications.
	 *
	 * @param FlysystemFile $targetFile the target file
	 *
	 * @returns StreamStat statistics about the final file, may differ from
	 *                     the source file due to normalization of orientation
	 *
	 * @throws ImageProcessingException
	 * @throws MediaFileOperationException
	 * @throws MediaFileUnsupportedException
	 * @throws ConfigurationException
	 */
	private function putSourceIntoFinalDestination(FlysystemFile $targetFile): StreamStat
	{
		try {
			if ($this->parameters->importMode->shallImportViaSymlink()) {
				if (!$targetFile->isLocalFile()) {
					throw new ConfigurationException('Symlinking is only supported on local filesystems');
				}
				$targetAbsolutePath = $targetFile->getAbsolutePath();
				$sourceAbsolutePath = $this->sourceFile->getAbsolutePath();
				\Safe\symlink($sourceAbsolutePath, $targetAbsolutePath);
				$streamStat = StreamStat::createFromLocalFile($this->sourceFile);
			} else {
				// Nothing to do for non-JPEGs or correctly oriented photos.
				// TODO: Why do we only normalize JPEG?
				$shallNormalize = $this->photo->type === 'image/jpeg' && $this->parameters->exifInfo->orientation !== 1;

				if ($shallNormalize) {
					$streamStat = $this->sourceImage->save($targetFile);
				} else {
					$streamStat = $targetFile->write($this->sourceFile->read());
					$this->sourceFile->close();
					$targetFile->close();
				}
				if ($this->parameters->importMode->shallDeleteImported()) {
					// This may throw an exception, if the original has been
					// readable, but is not writable
					// In this case, the media file will have been copied, but
					// cannot be "moved".
					try {
						$this->sourceFile->delete();
					} catch (MediaFileOperationException $e) {
						// If deletion failed, we do not cancel the whole
						// import, but fall back to copy-semantics and
						// log the exception
						report($e);
					}
				}
			}

			return $streamStat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Could move/copy/symlink source file to final destination', $e);
		}
	}

	/**
	 * TODO: This method is invoked too late, because the source file may already be deleted
	 * TODO: Move this into a proper Videohandler.
	 *
	 * @throws MediaFileOperationException
	 */
	protected function handleGoogleMotionPicture(): void
	{
		if ($this->parameters->exifInfo->microVideoOffset === 0) {
			return;
		}

		$videoLengthBytes = $this->parameters->exifInfo->microVideoOffset;
		$original = $this->photo->size_variants->getOriginal();
		$shortPathPhoto = $original->short_path;
		$fullPathPhoto = $original->full_path;
		$shortPathVideo = pathinfo($shortPathPhoto, PATHINFO_FILENAME) . '.mov';
		$fullPathVideo = pathinfo($fullPathPhoto, PATHINFO_FILENAME) . '.mov';

		try {
			// 1. Extract the video part
			$fp = fopen($fullPathPhoto, 'r');
			$fp_video = tmpfile(); // use a temporary file, will be deleted once closed

			// The MP4 file is located in the last bytes of the file
			fseek($fp, -1 * $videoLengthBytes, SEEK_END); // It needs to be negative
			$data = fread($fp, $videoLengthBytes);
			fwrite($fp_video, $data, $videoLengthBytes);

			// 2. Convert file from mp4 to mov, but keeping audio and video codec
			// This is needed to LivePhotosKit which only accepts mov files
			// Computation is fast, since codecs, resolution, framerate etc. remain unchanged

			/**
			 * ! check if we can use path instead of this ugly thing.
			 * TODO: Au contraire! If we ever want to be able to use non-local storage, we must stop using paths, but use file streams.
			 * TODO: Nonetheless, this ugliness should be properly encapsulated in an designated class.
			 */
			$ffmpeg = FFMpeg::create();
			$video = $ffmpeg->open(stream_get_meta_data($fp_video)['uri']);
			$format = new MOVFormat();
			// Add additional parameter to extract the first video stream
			$format->setAdditionalParameters(['-map', '0:0']);
			$video->save($format, $fullPathVideo);

			// 3. Close files ($fp_video will be again deleted)
			fclose($fp);
			fclose($fp_video);

			// Save file path; Checksum calculation not needed since
			// we do not perform matching for Google Motion Photos (as for iOS Live Photos)
			$this->photo->live_photo_short_path = $shortPathVideo;
			$this->photo->save();
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Unable to extract video from Google Motion Picture', $e);
		}
	}
}
