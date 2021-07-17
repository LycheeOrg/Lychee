<?php

namespace App\Actions\Photo\Strategies;

use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Image\ImageHandlerInterface;
use App\ModelFunctions\MOVFormat;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\SizeVariant;
use FFMpeg\FFMpeg;

class AddStandaloneStrategy extends AddBaseStrategy
{
	public function __construct(AddStrategyParameters $parameters)
	{
		parent::__construct($parameters, new Photo());
	}

	public function do(): Photo
	{
		// Create and save "bare" photo object without size variants
		$this->hydrateMetadata();
		$this->photo->public = $this->parameters->public;
		$this->photo->star = $this->parameters->star;
		$this->setParentAndOwnership();
		$this->photo->save();

		// Initialize factory for size variants
		/** @var SizeVariantNamingStrategy $namingStrategy */
		$namingStrategy = resolve(SizeVariantNamingStrategy::class);
		$namingStrategy->setFallbackExtension(
			$this->parameters->sourceFileInfo->getOriginalFileExtension()
		);
		/** @var SizeVariantFactory $sizeVariantFactory */
		$sizeVariantFactory = resolve(SizeVariantFactory::class);
		$sizeVariantFactory->init($this->photo, $namingStrategy);

		// Create size variant for original
		$original = $sizeVariantFactory->createOriginal(
			$this->parameters->info['width'],
			$this->parameters->info['height']
		);
		$this->putSourceIntoFinalDestination($original->full_path);
		// The orientation can only be normalized after the source file has
		// been put into its final destination, because we need an actual file
		// which can be rotated if we import the source file from another
		// directory on the server (i.e. file copy) or if we import the source
		// from a link (i.e. file download),
		$this->normalizeOrientation($original);

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
	 * The method does not actual modify the underlying file if it is only a
	 * symlink.
	 * This method also updated the attributes {@link SizeVariant::$width},
	 * {@link SizeVariant::$height} and {@link Photo::$filesize} to the new
	 * values after rotation.
	 *
	 * @param SizeVariant $original the original size variant
	 */
	protected function normalizeOrientation(SizeVariant $original): void
	{
		$orientation = $this->parameters->info['orientation'];
		$fullPath = $original->full_path;
		if ($this->photo->type === 'image/jpeg' && $orientation != 1) {
			// If we are importing via symlink, we don't actually overwrite
			// the source but we still need to fix the dimensions.
			/** @var ImageHandlerInterface $imageHandler */
			$imageHandler = resolve(ImageHandlerInterface::class);
			$newDim = $imageHandler->autoRotate(
				$fullPath,
				$orientation,
				$this->parameters->importMode->shallImportViaSymlink()
			);

			if ($newDim !== [false, false]) {
				$original->width = $newDim['width'];
				$original->height = $newDim['height'];
				// If the image has actually been rotated, the size may
				// have changed.
				$this->photo->filesize = (int) filesize($fullPath);
			}
		}

		// Set original date
		if ($this->parameters->info['taken_at'] !== null) {
			@touch($fullPath, $this->parameters->info['taken_at']->getTimestamp());
		}
	}

	protected function handleGoogleMotionPicture(): void
	{
		if (empty($this->parameters->info['MicroVideoOffset'])) {
			return;
		}

		$videoLengthBytes = intval($this->parameters->info['MicroVideoOffset']);
		$original = $this->photo->size_variants->getSizeVariant(SizeVariant::ORIGINAL);
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
		} catch (\Throwable $e) {
			Logs::error(__METHOD__, __LINE__, $e->getMessage());
			throw new \RuntimeException('unable to extract video from Google Motion Picture', 0, $e);
		}
	}
}
