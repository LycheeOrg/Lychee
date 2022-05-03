<?php

namespace App\Image;

use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Exceptions\ModelDBException;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\SizeVariant;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Exception\ExecutableNotFoundException;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Illuminate\Support\Collection;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class SizeVariantDefaultFactory extends SizeVariantFactory
{
	public const THUMBNAIL_DIM = 200;
	public const THUMBNAIL2X_DIM = 400;

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

	/**
	 * @throws ExternalComponentMissingException
	 * @throws ConfigurationException
	 * @throws MediaFileOperationException
	 * @throws IllegalOrderOfOperationException
	 * @throws MediaFileUnsupportedException
	 */
	protected function extractReferenceImage(): void
	{
		$original = $this->photo->size_variants->getOriginal();
		if ($this->photo->isRaw()) {
			$this->extractReferenceFromRaw($original->full_path, $original->width, $original->height);
		} elseif ($this->photo->isVideo()) {
			if (empty($this->photo->aperture)) {
				throw new MediaFileOperationException('Media file is reported to be a video, but aperture (aka duration) has not been extracted');
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
			unlink($this->referenceFullPath);
		}
		$this->referenceFullPath = '';
	}

	/**
	 * Extracts a reference image from a raw file.
	 *
	 * @param string $fullPath The full path to the original (raw) file
	 * @param int    $width    The original width
	 * @param int    $height   The original height
	 *
	 * @throws ExternalComponentMissingException
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	protected function extractReferenceFromRaw(string $fullPath, int $width, int $height): void
	{
		// we need imagick to do the job
		if (!Configs::hasImagick()) {
			throw new ExternalComponentMissingException('Saving JPG of raw file failed: Imagick not installed.');
		}
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
		// test if Imagick supports the filetype
		// Query return file extensions as all upper case
		if (!in_array(strtoupper($ext), \Imagick::queryformats())) {
			throw new MediaFileUnsupportedException('Filetype ' . $ext . ' not supported by Imagick.');
		}
		$this->createTmpPathForReferenceJPEG();
		$this->imageHandler->scale($fullPath, $this->referenceFullPath, $width, $height, $this->referenceWidth, $this->referenceHeight);
	}

	/**
	 * Extracts a reference image from a video file at the given position.
	 *
	 * @param string $fullPath
	 * @param float  $framePosition The temporal position in seconds of the
	 *                              frame to be extracted
	 *
	 * @throws ConfigurationException
	 * @throws ExternalComponentMissingException
	 * @throws MediaFileOperationException
	 */
	protected function extractReferenceFromVideo(string $fullPath, float $framePosition): void
	{
		if (!Configs::hasFFmpeg()) {
			throw new ConfigurationException('FFmpeg is disabled by configuration');
		}
		try {
			$this->createTmpPathForReferenceJPEG();
			$ffmpeg = FFMpeg::create();
			/** @var Video $video */
			$video = $ffmpeg->open($fullPath);
			$this->extractFrame($video, $framePosition);
		} catch (ExecutableNotFoundException $e) {
			throw new ExternalComponentMissingException('FFmpeg not found', $e);
		} catch (InvalidArgumentException $e) {
			throw new MediaFileOperationException('FFmpeg could not open media file', $e);
		} catch (MediaFileOperationException) {
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
	 * @throws MediaFileOperationException thrown, if FFmpeg failed to extract a frame
	 */
	protected function extractFrame(Video $video, float $framePosition): void
	{
		$errMsg = 'Failed to extract snapshot from video ' . $this->referenceFullPath . ' at position ' . $framePosition;
		try {
			$dim = $video->getStreams()->videos()->first()->getDimensions();
			$frame = $video->frame(TimeCode::fromSeconds($framePosition));
			$frame->save($this->referenceFullPath);
			$this->referenceWidth = $dim->getWidth();
			$this->referenceHeight = $dim->getHeight();
		} catch (\Throwable $e) {
			throw new MediaFileOperationException($errMsg, $e);
		}
		if (!file_exists($this->referenceFullPath) || filesize($this->referenceFullPath) == 0) {
			throw new MediaFileOperationException($errMsg);
		}
		if (Configs::get_value('lossless_optimization')) {
			ImageOptimizer::optimize($this->referenceFullPath);
		}
	}

	/**
	 * Creates a temporary path to store the extracted reference image.
	 *
	 * This method modifies `referenceFullPath` and also sets `needsCleanup`
	 * to true such that the file which is stored at `referenceFullPath` will
	 * be removed by {@link SizeVariantFactory::cleanup()}.
	 */
	protected function createTmpPathForReferenceJPEG(): void
	{
		$this->referenceFullPath = (new TemporaryLocalFile('.jpg'))->getAbsolutePath();
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
			throw new InvalidSizeVariantException('createSizeVariant() must not be used to create original size, use createOriginal() instead');
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
			throw new InvalidSizeVariantException('createSizeVariantCond() must not be used to create original size, use createOriginal() instead');
		}
		if (!$this->isEnabledByConfiguration($sizeVariant)) {
			return null;
		}
		// Don't generate medium size variants for videos, because the current web front-end has no use for it. Let's save some storage space.
		if ($this->photo->isVideo() && ($sizeVariant === SizeVariant::MEDIUM || $sizeVariant === SizeVariant::MEDIUM2X)) {
			return null;
		}
		if (empty($this->referenceFullPath)) {
			$this->extractReferenceImage();
		}

		list($maxWidth, $maxHeight) = $this->getMaxDimensions($sizeVariant);

		if ($sizeVariant === SizeVariant::THUMB) {
			$isLargeEnough = true;
		} elseif ($sizeVariant === SizeVariant::THUMB2X) {
			$isLargeEnough = $this->referenceWidth > $maxWidth && $this->referenceHeight > $maxHeight;
		} else {
			$isLargeEnough = $this->referenceWidth > $maxWidth || $this->referenceHeight > $maxHeight;
		}

		return $isLargeEnough ?
			$this->createSizeVariantInternal(
				$sizeVariant,
				$maxWidth,
				$maxHeight
			) :
			null;
	}

	/**
	 * @throws ModelDBException
	 * @throws IllegalOrderOfOperationException
	 * @throws MediaFileOperationException
	 * @throws MediaFileUnsupportedException
	 * @throws InvalidSizeVariantException
	 */
	protected function createSizeVariantInternal(int $sizeVariant, int $maxWidth, int $maxHeight): SizeVariant
	{
		$shortPath = $this->namingStrategy->generateShortPath($sizeVariant);

		$sv = $this->photo->size_variants->getSizeVariant($sizeVariant);
		if (!$sv) {
			try {
				// Create size variant with dummy filesize, because full path
				// is for now required to crop/scale.
				// However, before cropping/scaling, the real filesize is not
				// known yet.
				// Ideally, the media file would be created independently of
				// variant entry, and then stored.
				// TODO: Use `MediaFile` here, let the ImageHandler operate on the MediaFile (without using paths at all) and then create the size variant in the end using the final and correct file size right away.
				$sv = $this->photo->size_variants->create($sizeVariant, $shortPath, $maxWidth, $maxHeight, 0);
				$svAbsolutePath = $sv->getFile()->getAbsolutePath();
				if ($sizeVariant === SizeVariant::THUMB || $sizeVariant === SizeVariant::THUMB2X) {
					$this->imageHandler->crop($this->referenceFullPath, $svAbsolutePath, $sv->width, $sv->height);
					$sv->filesize = filesize($svAbsolutePath);
					$sv->save();
				} else {
					$resWidth = $resHeight = 0;
					$this->imageHandler->scale($this->referenceFullPath, $svAbsolutePath, $sv->width, $sv->height, $resWidth, $resHeight);
					$sv->filesize = filesize($svAbsolutePath);
					$sv->width = $resWidth;
					$sv->height = $resHeight;
					$sv->save();
				}
			} catch (MediaFileOperationException|MediaFileUnsupportedException $e) {
				// If scaling/cropping has failed, remove the freshly created DB entity again
				// This will also take care of removing a potentially created file from storage
				try {
					$sv->delete();
				} catch (\Throwable) {
					// We are already in an (outer) error handling, we cannot
					// do anything about this inner problem
				}
				throw $e;
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
	 *
	 * @throws InvalidSizeVariantException
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
				throw new InvalidSizeVariantException('unknown size variant: ' . $sizeVariant);
		}

		return [$maxWidth, $maxHeight];
	}

	/**
	 * Checks whether the requested size variant is enabled by configuration.
	 *
	 * This function always returns true, for size variants which are not
	 * configurable and are always enabled (e.g. a thumb).
	 * Hence, it is safe to call this function for all size variants.
	 * For size variants which may be enabled/disabled through configuration at
	 * runtime, the method only returns true, if
	 *
	 *  1. the size variant is enabled, and
	 *  2. the allowed maximum width or maximum height is not zero.
	 *
	 * In other words, even if a size variant is enabled, this function
	 * still returns false, if both the allowed maximum width and height
	 * equal zero.
	 *
	 * @param int $sizeVariant the indicated size variant
	 *
	 * @return bool true, if the size variant is enabled and the allowed width
	 *              or height is unequal to zero
	 *
	 * @throws InvalidSizeVariantException
	 */
	protected function isEnabledByConfiguration(int $sizeVariant): bool
	{
		list($maxWidth, $maxHeight) = $this->getMaxDimensions($sizeVariant);
		if ($maxWidth === 0 && $maxHeight === 0) {
			return false;
		}

		return match ($sizeVariant) {
			SizeVariant::MEDIUM2X => Configs::get_value('medium_2x', 0) == 1,
			SizeVariant::SMALL2X => Configs::get_value('small_2x', 0) == 1,
			SizeVariant::THUMB2X => Configs::get_value('thumb_2x', 0) == 1,
			SizeVariant::SMALL, SizeVariant::MEDIUM, SizeVariant::THUMB => true,
			default => throw new InvalidSizeVariantException('unknown size variant: ' . $sizeVariant),
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function createOriginal(int $width, int $height, int $filesize): SizeVariant
	{
		return $this->photo->size_variants->create(
			SizeVariant::ORIGINAL,
			$this->namingStrategy->generateShortPath(SizeVariant::ORIGINAL),
			$width,
			$height,
			$filesize
		);
	}
}
