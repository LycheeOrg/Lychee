<?php

namespace App\Actions\Photo\Strategies;

use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Image\ImageHandlerInterface;
use App\Image\MediaFile;
use App\Image\TemporaryLocalFile;
use App\Metadata\Extractor;
use App\ModelFunctions\MOVFormat;
use App\Models\Logs;
use App\Models\Photo;
use FFMpeg\FFMpeg;

class AddStandaloneStrategy extends AddBaseStrategy
{
	public function __construct(AddStrategyParameters $parameters)
	{
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
	}

	public function do(): Photo
	{
		// Create and save "bare" photo object without size variants
		$this->hydrateMetadata();
		$this->photo->is_public = $this->parameters->is_public;
		$this->photo->is_starred = $this->parameters->is_starred;
		$this->setParentAndOwnership();
		$this->photo->save();

		$this->normalizeOrientation();

		// Initialize factory for size variants
		/** @var SizeVariantNamingStrategy $namingStrategy */
		$namingStrategy = resolve(SizeVariantNamingStrategy::class);
		$namingStrategy->setFallbackExtension(
			$this->parameters->sourceFileInfo->getOriginalExtension()
		);
		/** @var SizeVariantFactory $sizeVariantFactory */
		$sizeVariantFactory = resolve(SizeVariantFactory::class);
		$sizeVariantFactory->init($this->photo, $namingStrategy);

		// Create size variant for original
		$original = $sizeVariantFactory->createOriginal(
			$this->parameters->info['width'],
			$this->parameters->info['height'],
			$this->parameters->info['filesize']
		);
		try {
			$this->putSourceIntoFinalDestination($original->short_path);
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
			Logs::error(__METHOD__, __LINE__, 'Failed to generate size variants, error was ' . $t->getMessage());
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
	 * This method also updates the attribute {@link Photo::$checksum} to the new value after rotation.
	 */
	protected function normalizeOrientation(): void
	{
		$orientation = $this->parameters->info['orientation'];
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
			$info = $this->parameters->sourceFileInfo;
			$file = $info->getFile();
			$tmpFile = new TemporaryLocalFile();
			$tmpFile->write($file->read());
			$file->close();
			// Reset source file info to the new temporary and ensure that
			// it will be deleted later
			$this->parameters->sourceFileInfo = SourceFileInfo::createByTempFile(
				$info->getOriginalName(),
				$info->getOriginalExtension(),
				$tmpFile
			);
			$this->parameters->importMode->setDeleteImported(true);
		}

		/** @var ImageHandlerInterface $imageHandler */
		$imageHandler = resolve(ImageHandlerInterface::class);

		$absolutePath = $this->parameters->sourceFileInfo->getFile()->getAbsolutePath();
		// If we are importing via symlink, we don't actually overwrite
		// the source but we still need to fix the dimensions.
		$newDim = $imageHandler->autoRotate(
			$absolutePath,
			$orientation,
			$this->parameters->importMode->shallImportViaSymlink()
		);

		if ($newDim !== [false, false]) {
			// If the image has actually been rotated, the size
			// and the checksum may have changed.
			/* @var  Extractor $metadataExtractor */
			$metadataExtractor = resolve(Extractor::class);
			$this->photo->checksum = $metadataExtractor->checksum($absolutePath);
			// stat info (filesize, access mode etc) are cached by PHP to avoid costly I/O calls.
			// If cache if not cleared, the size before rotation is used and later yields incorrect value.
			clearstatcache(true, $absolutePath);
			// Update filesize for later use e.g. when creating variants
			$this->parameters->info['filesize'] = $metadataExtractor->filesize($absolutePath);
			$this->photo->save();
		}
	}

	protected function handleGoogleMotionPicture(): void
	{
		if (empty($this->parameters->info['MicroVideoOffset'])) {
			return;
		}

		$videoLengthBytes = intval($this->parameters->info['MicroVideoOffset']);
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
			Logs::error(__METHOD__, __LINE__, $e->getMessage());
			throw new \RuntimeException('unable to extract video from Google Motion Picture', 0, $e);
		}
	}
}
