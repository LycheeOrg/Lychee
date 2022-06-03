<?php

namespace App\Actions\Photo\Strategies;

use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Exceptions\ModelDBException;
use App\Image\ImageHandlerInterface;
use App\Image\MediaFile;
use App\Image\NativeLocalFile;
use App\Image\TemporaryLocalFile;
use App\Metadata\Extractor;
use App\ModelFunctions\MOVFormat;
use App\Models\Photo;
use FFMpeg\FFMpeg;
use Illuminate\Contracts\Container\BindingResolutionException;

class AddStandaloneStrategy extends AddBaseStrategy
{
	protected ImageHandlerInterface $imageHandler;
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
			$this->imageHandler = resolve(ImageHandlerInterface::class);
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

		$this->photo->original_checksum = Extractor::checksum($this->sourceFile);
		$this->normalizeOrientation();
		$this->photo->checksum = Extractor::checksum($this->sourceFile);

		$this->photo->save();

		// Initialize factory for size variants
		/** @var SizeVariantNamingStrategy $namingStrategy */
		$namingStrategy = resolve(SizeVariantNamingStrategy::class);
		$namingStrategy->setFallbackExtension(
			$this->sourceFile->getOriginalExtension()
		);
		/** @var SizeVariantFactory $sizeVariantFactory */
		$sizeVariantFactory = resolve(SizeVariantFactory::class);
		$sizeVariantFactory->init($this->photo, $namingStrategy);

		/**
		 * Create size variant for original
		 * Exception `IllegalOrderOfOperations` is never thrown, because we
		 * have saved the photo above.
		 *
		 * @noinspection PhpUnhandledExceptionInspection
		 */
		$original = $sizeVariantFactory->createOriginal(
			$this->parameters->exifInfo->width,
			$this->parameters->exifInfo->height,
			$this->sourceFile->getFilesize()
		);
		try {
			$this->putSourceIntoFinalDestination($this->sourceFile, $original->short_path);
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

		// Clean up factory
		$sizeVariantFactory->cleanup();

		return $this->photo;
	}

	/**
	 * Correct orientation of original size variant based on EXIF data.
	 *
	 * **ATTENTION:** As a side effect of the method, the
	 * {@link MediaFile}-instance stored in {@link AddStandaloneStrategy::$parameters}
	 * might change.
	 *
	 * There are 5 possibilities for the source file:
	 *
	 *  1. the source file has been uploaded by a client
	 *  2. the source file has been downloaded from a remote server
	 *  3. the source file is a local file on the server and
	 *      a) shall be deleted
	 *      b) shall be copied
	 *      c) shall be symlinked
	 *
	 * Cases 1 trough 3a can be treated identically:
	 * In all three cases we have a local (possibly temporary) file which
	 * will be removed anyway and thus can be modified in place.
	 *
	 * In case 3b, we make a local, temporary copy first and then proceed as
	 * in the first 3 cases.
	 * This is also the case which changes the {@link MediaFile}-instance.
	 *
	 * In case 3c, the method does not actually modify the file.
	 *
	 * This method also updates the attribute {@link Photo::$checksum} to the
	 * new value after rotation.
	 *
	 * @throws MediaFileOperationException
	 * @throws ModelDBException
	 * @throws MediaFileUnsupportedException
	 */
	protected function normalizeOrientation(): void
	{
		$orientation = $this->parameters->exifInfo->orientation;
		if ($this->photo->type !== 'image/jpeg' || $orientation == 1) {
			// Nothing to do for non-JPEGs or correctly oriented photos.
			return;
		}

		if (
			!$this->parameters->importMode->shallDeleteImported() &&
			!$this->parameters->importMode->shallImportViaSymlink()
		) {
			// This is case 3b, the original shall neither be deleted
			// nor symlinked.
			// So lets make a deep-copy first which can be rotated safely.
			$tmpFile = new TemporaryLocalFile($this->sourceFile->getExtension(), $this->sourceFile->getBasename());
			$tmpFile->write($this->sourceFile->read());
			$this->sourceFile->close();
			$this->sourceFile = $tmpFile;
			// Reset source file info to the new temporary and ensure that
			// it will be deleted later
			$this->parameters->importMode->setDeleteImported(true);
		}

		$absolutePath = $this->sourceFile->getAbsolutePath();
		// If we are importing via symlink, we don't actually overwrite
		// the source, but we still need to fix the dimensions.
		$this->imageHandler->autoRotate(
			$absolutePath,
			$orientation,
			$this->parameters->importMode->shallImportViaSymlink()
		);

		// stat info (filesize, access mode, etc.) are cached by PHP to avoid
		// costly I/O calls.
		// If cache is not cleared, the size before rotation is used and later
		// yields an incorrect value.
		clearstatcache(true, $absolutePath);
	}

	/**
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
