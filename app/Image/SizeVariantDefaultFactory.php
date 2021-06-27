<?php

namespace App\Image;

use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Facades\Helpers;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\SizeVariant;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Illuminate\Support\Collection;

class SizeVariantDefaultFactory extends SizeVariantFactory
{
	const THUMBNAIL_DIM = 200;
	const THUMBNAIL2X_DIM = 400;

	/** @var ImageHandlerInterface the image handler (gd, imagick, ...) which is used to generate image files */
	protected ImageHandlerInterface $imageHandler;
	protected ?Photo $photo = null;
	protected ?SizeVariantNamingStrategy $namingStrategy = null;
	protected string $referenceFullPath = '';
	protected int $referenceWidth = 0;
	protected int $referenceHeight = 0;
	protected bool $needsCleanup = false;

	public function __construct(ImageHandlerInterface $imageHandler)
	{
		$this->imageHandler = $imageHandler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function init(Photo $photo, ?SizeVariantNamingStrategy $namingStrategy = null): void
	{
		if ($this->photo) {
			$this->cleanup();
		}
		$this->photo = $photo;
		if ($namingStrategy) {
			$this->namingStrategy = $namingStrategy;
		} elseif (!$this->namingStrategy) {
			$this->namingStrategy = resolve(SizeVariantNamingStrategy::class);
		}
		// Ensure that the naming strategy is linked to this photo
		$this->namingStrategy->setPhoto($this->photo);
	}

	protected function extractReferenceImage(): void
	{
		$original = $this->photo->size_variants->getSizeVariant(SizeVariant::ORIGINAL);
		if ($this->photo->isRaw()) {
			$this->extractReferenceFromRaw($original->full_path, $original->width, $original->height);
		} elseif ($this->photo->isVideo()) {
			if (empty($this->photo->aperture)) {
				Logs::error(__METHOD__, __LINE__, 'Media file is reported to be a video, but aperture (aka duration) has not been extracted');
				throw new \RuntimeException('Media file is reported to be a video, but aperture (aka duration) has not been extracted');
			}
			$position = floatval($this->photo->aperture) / 2;
			$this->extractReferenceFromVideo($original->full_path, $position);
		} else {
			$this->referenceFullPath = $original->full_path;
			$this->referenceWidth = $original->width;
			$this->referenceHeight = $original->height;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function cleanup(): void
	{
		$this->photo = null;
		$this->namingStrategy = null;
		if ($this->needsCleanup) {
			@unlink($this->referenceFullPath);
		}
	}

	/**
	 * Extracts a reference image from a raw file.
	 *
	 * @param string $fullPath The full path to the original (raw) file
	 * @param int    $width    The original width
	 * @param int    $height   The original height
	 */
	protected function extractReferenceFromRaw(string $fullPath, int $width, int $height): void
	{
		// we need imagick to do the job
		if (!Configs::hasImagick()) {
			$msg = 'Saving JPG of raw file failed: Imagick not installed.';
			Logs::notice(__METHOD__, __LINE__, $msg);
			throw new \RuntimeException($msg);
		}
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
		// test if Imagick supports the filetype
		// Query return file extensions as all upper case
		if (!in_array(strtoupper($ext), \Imagick::queryformats())) {
			$msg = 'Filetype ' . $ext . ' not supported by Imagick.';
			Logs::notice(__METHOD__, __LINE__, $msg);
			throw new \RuntimeException($msg);
		}
		$this->createTmpPathForReference();
		try {
			$this->imageHandler->scale($fullPath, $this->referenceFullPath, $width, $height, $this->referenceWidth, $this->referenceHeight);
		} catch (\Throwable $e) {
			$msg = 'Failed to create JPG from raw file ' . $fullPath;
			Logs::error(__METHOD__, __LINE__, $msg);
			throw new \RuntimeException($msg, 0, $e);
		}
	}

	/**
	 * Extracts a reference image from a video file at the given position.
	 *
	 * @param string $fullPath
	 * @param float  $framePosition The temporal position in seconds of the frame to be extracted
	 */
	protected function extractReferenceFromVideo(string $fullPath, float $framePosition): void
	{
		if (!Configs::hasFFmpeg()) {
			Logs::notice(__METHOD__, __LINE__, 'Failed to extract reference image from video as FFmpeg is unavailable');
			throw new \RuntimeException('Failed to extract reference image from video as FFmpeg is unavailable');
		}
		$this->createTmpPathForReference();
		$ffmpeg = FFMpeg::create();
		/** @var Video $video */
		$video = $ffmpeg->open($fullPath);
		try {
			$this->extractFrame($video, $framePosition);
		} catch (\RuntimeException $e) {
			Logs::notice(__METHOD__, __LINE__, 'Fallback: Try to extract snapshot at position 0');
			$this->extractFrame($video, 0);
		}
	}

	/**
	 * Extracts a frame from a loaded `Video` object at the given position.
	 *
	 * @param Video $video         the video object
	 * @param float $framePosition the position in seconds
	 *
	 * @throws \RuntimeException thrown, if FFmpeg failed to extract a frame
	 */
	protected function extractFrame(Video $video, float $framePosition): void
	{
		$errMsg = 'Failed to extract snapshot from video ' . $this->referenceFullPath . ' at position ' . $framePosition;
		try {
			$frame = $video->frame(TimeCode::fromSeconds($framePosition));
			$frame->save($this->referenceFullPath);
		} catch (\RuntimeException $e) {
			Logs::error(__METHOD__, __LINE__, $errMsg);
			throw new \RuntimeException($errMsg, 0, $e);
		}
		if (!file_exists($this->referenceFullPath) || filesize($this->referenceFullPath) == 0) {
			throw new \RuntimeException($errMsg);
		}
		if (Configs::get_value('lossless_optimization')) {
			ImageOptimizer::optimize($this->referenceFullPath);
		}
	}

	/**
	 * Creates a temporary path to store the extracted reference image.
	 *
	 * This method modifies `referenceFullPath` and also set `needsCleanup`
	 * to true such that the file which is stored at `referenceFullPath` will
	 * be removed by {@link SizeVariantFactory::cleanup()}.
	 */
	protected function createTmpPathForReference(): void
	{
		$this->referenceFullPath = Helpers::createTemporaryFile('.jpeg');
		$this->needsCleanup = true;
		Logs::notice(__METHOD__, __LINE__, 'Saving JPG of raw/video file to ' . $this->referenceFullPath);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createSizeVariants(): Collection
	{
		$allVariants = [
			SizeVariant::THUMB,
			SizeVariant::THUMB2X,
			SizeVariant::SMALL,
			SizeVariant::SMALL2X,
			SizeVariant::MEDIUM,
			SizeVariant::MEDIUM2X,
		];
		$collection = new Collection();

		foreach ($allVariants as $variant) {
			$sv = $this->createSizeVariantCond($variant);
			if ($sv) {
				$collection->add($sv);
			}
		}

		return $collection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createSizeVariant(int $sizeVariant): SizeVariant
	{
		if ($sizeVariant === SizeVariant::ORIGINAL) {
			throw new \InvalidArgumentException('createSizeVariant() must not be used to create original size, use createOriginal() instead');
		}
		if (empty($this->referenceFullPath)) {
			$this->extractReferenceImage();
		}
		list($maxWidth, $maxHeight) = $this->getMaxDimensions($sizeVariant);

		return $this->createSizeVariantInternal(
			$sizeVariant, $maxWidth, $maxHeight
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createSizeVariantCond(int $sizeVariant): ?SizeVariant
	{
		if ($sizeVariant === SizeVariant::ORIGINAL) {
			throw new \InvalidArgumentException('createSizeVariantCond() must not be used to create original size, use createOriginal() instead');
		}
		if (!$this->isEnabledByConfiguration($sizeVariant)) {
			return null;
		}
		if (empty($this->referenceFullPath)) {
			$this->extractReferenceImage();
		}

		list($maxWidth, $maxHeight) = $this->getMaxDimensions($sizeVariant);

		if ($sizeVariant === SizeVariant::THUMB || $sizeVariant === SizeVariant::THUMB2X) {
			$isLargeEnough = $this->referenceWidth > $maxWidth && $this->referenceHeight > $maxHeight;
		} else {
			$isLargeEnough = $this->referenceWidth > $maxWidth || $this->referenceHeight > $maxHeight;
		}

		if ($isLargeEnough) {
			return $this->createSizeVariantInternal(
				$sizeVariant,
				$maxWidth,
				$maxHeight
			);
		} else {
			Logs::notice(__METHOD__, __LINE__, 'Did not create size variant ' . $sizeVariant . ';  original image is too small: ' . $maxWidth . 'x' . $maxHeight . '!');

			return null;
		}
	}

	protected function createSizeVariantInternal(int $sizeVariant, int $maxWidth, int $maxHeight): SizeVariant
	{
		$shortPath = $this->namingStrategy->generateShortPath($sizeVariant);

		$sv = $this->photo->size_variants->getSizeVariant($sizeVariant);
		if (!$sv) {
			$sv = $this->photo->size_variants->createSizeVariant($sizeVariant, $shortPath, $maxWidth, $maxHeight);
			if ($sizeVariant === SizeVariant::THUMB || $sizeVariant === SizeVariant::THUMB2X) {
				$success = $this->imageHandler->crop($this->referenceFullPath, $sv->full_path, $sv->width, $sv->height);
			} else {
				$resWidth = $resHeight = 0;
				$success = $this->imageHandler->scale($this->referenceFullPath, $sv->full_path, $sv->width, $sv->height, $resWidth, $resHeight);
				$sv->width = $resWidth;
				$sv->height = $resHeight;
				$sv->save();
			}
			if (!$success) {
				Logs::error(__METHOD__, __LINE__, 'Failed to resize image: ' . $this->referenceFullPath);
				// If scaling/cropping has failed, remove the freshly create DB entity again
				// This will also take care of removing a potentially created file from storage
				$sv->delete();
				throw new \RuntimeException('Failed to resize image: ' . $this->referenceFullPath);
			}
		}

		return $sv;
	}

	/**
	 * Determines the maximum dimensions of the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return int[] an array with exactly two integers, the first integer is
	 *               the width, the second integer is the height
	 */
	protected function getMaxDimensions(int $sizeVariant): array
	{
		switch ($sizeVariant) {
			case SizeVariant::MEDIUM2X:
				$maxWidth = 2 * intval(Configs::get_value('medium_max_width'));
				$maxHeight = 2 * intval(Configs::get_value('medium_max_height'));
				break;
			case SizeVariant::MEDIUM:
				$maxWidth = intval(Configs::get_value('medium_max_width'));
				$maxHeight = intval(Configs::get_value('medium_max_height'));
				break;
			case SizeVariant::SMALL2X:
				$maxWidth = 2 * intval(Configs::get_value('small_max_width'));
				$maxHeight = 2 * intval(Configs::get_value('small_max_height'));
				break;
			case SizeVariant::SMALL:
				$maxWidth = intval(Configs::get_value('small_max_width'));
				$maxHeight = intval(Configs::get_value('small_max_height'));
				break;
			case SizeVariant::THUMB2X:
				$maxWidth = self::THUMBNAIL2X_DIM;
				$maxHeight = self::THUMBNAIL2X_DIM;
				break;
			case SizeVariant::THUMB:
				$maxWidth = self::THUMBNAIL_DIM;
				$maxHeight = self::THUMBNAIL_DIM;
				break;
			default:
				throw new \InvalidArgumentException('unknown size variant: ' . $sizeVariant);
		}

		return [$maxWidth, $maxHeight];
	}

	protected function isEnabledByConfiguration(int $sizeVariant): bool
	{
		switch ($sizeVariant) {
			case SizeVariant::MEDIUM2X:
				return Configs::get_value('medium_2x', 0) == 1;
			case SizeVariant::SMALL2X:
				return Configs::get_value('small_2x', 0) == 1;
			case SizeVariant::THUMB2X:
				return Configs::get_value('thumb_2x', 0) == 1;
			case SizeVariant::SMALL:
			case SizeVariant::MEDIUM:
			case SizeVariant::THUMB:
				return true;
			default:
				throw new \InvalidArgumentException('unknown size variant: ' . $sizeVariant);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function createOriginal(int $width, int $height): SizeVariant
	{
		return $this->photo->size_variants->createSizeVariant(
			SizeVariant::ORIGINAL,
			$this->namingStrategy->generateShortPath(SizeVariant::ORIGINAL),
			$width,
			$height
		);
	}
}
