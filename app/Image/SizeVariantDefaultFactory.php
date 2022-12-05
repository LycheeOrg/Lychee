<?php

namespace App\Image;

use App\Contracts\LycheeException;
use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\DTO\ImageDimension;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Models\Configs;
use App\Models\Extensions\SizeVariantType;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

class SizeVariantDefaultFactory extends SizeVariantFactory
{
	public const THUMBNAIL_DIM = 200;
	public const THUMBNAIL2X_DIM = 400;

	/** @var ImageHandlerInterface the image handler (gd, imagick, ...) which is used to generate image files */
	protected ImageHandlerInterface $referenceImage;
	protected ?Photo $photo = null;
	protected ?SizeVariantNamingStrategy $namingStrategy = null;

	/**
	 * {@inheritDoc}
	 */
	public function init(Photo $photo, ?ImageHandlerInterface $referenceImage = null, ?SizeVariantNamingStrategy $namingStrategy = null): void
	{
		try {
			$this->photo = $photo;
			if ($referenceImage !== null && $referenceImage->isLoaded()) {
				$this->referenceImage = $referenceImage;
			} else {
				$this->loadReferenceImage();
			}
			$this->namingStrategy = $namingStrategy ?? resolve(SizeVariantNamingStrategy::class);
			// Ensure that the naming strategy is linked to this photo
			$this->namingStrategy->setPhoto($this->photo);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}

	/**
	 * Loads the reference image from the original size variant of the associated photo.
	 *
	 * @throws ExternalComponentMissingException
	 * @throws ConfigurationException
	 * @throws MediaFileOperationException
	 * @throws IllegalOrderOfOperationException
	 * @throws MediaFileUnsupportedException
	 * @throws ImageProcessingException
	 */
	protected function loadReferenceImage(): void
	{
		$originalFile = $this->photo->size_variants->getOriginal()->getFile();

		if (!$this->photo->isVideo()) {
			$this->referenceImage = new ImageHandler();
			$this->referenceImage->load($originalFile);
		} else {
			if ($originalFile->isLocalFile()) {
				// If the original size variant is hosted locally,
				// we can directly take it as a source file
				$sourceFile = $originalFile->toLocalFile();
			} else {
				// If the original size variant is hosted remotely,
				// we must download it first; we exploit the temporary file
				// for that
				$sourceFile = new TemporaryLocalFile($originalFile->getOriginalExtension(), $this->photo->title);
				$sourceFile->write($originalFile->read(), false, $this->photo->type);
			}

			$videoHandler = new VideoHandler();
			$videoHandler->load($sourceFile);
			$position = is_numeric($this->photo->aperture) ? floatval($this->photo->aperture) / 2 : 0.0;
			$this->referenceImage = $videoHandler->extractFrame($position);

			// Clean up
			if ($sourceFile instanceof TemporaryLocalFile) {
				$sourceFile->delete();
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function createSizeVariants(): Collection
	{
		$allVariants = [
			SizeVariantType::THUMB,
			SizeVariantType::THUMB2X,
			SizeVariantType::SMALL,
			SizeVariantType::SMALL2X,
			SizeVariantType::MEDIUM,
			SizeVariantType::MEDIUM2X,
		];
		$collection = new Collection();

		foreach ($allVariants as $variant) {
			$sv = $this->createSizeVariantCond($variant);
			if ($sv !== null) {
				$collection->add($sv);
			}
		}

		return $collection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createSizeVariantCond(SizeVariantType $sizeVariant): ?SizeVariant
	{
		if ($sizeVariant === SizeVariantType::ORIGINAL) {
			throw new InvalidSizeVariantException('createSizeVariantCond() must not be used to create original size');
		}
		if (!$this->isEnabledByConfiguration($sizeVariant)) {
			return null;
		}
		// Don't generate medium size variants for videos, because the current web front-end has no use for it. Let's save some storage space.
		if ($this->photo->isVideo() && ($sizeVariant === SizeVariantType::MEDIUM || $sizeVariant === SizeVariantType::MEDIUM2X)) {
			return null;
		}
		// Don't re-create existing size variant
		if ($this->photo->size_variants->getSizeVariant($sizeVariant) !== null) {
			return null;
		}

		$maxDim = $this->getMaxDimensions($sizeVariant);
		$realDim = $this->referenceImage->getDimensions();

		$isLargeEnough = match ($sizeVariant) {
			SizeVariantType::THUMB => true,
			SizeVariantType::THUMB2X => $realDim->width >= $maxDim->width && $realDim->height >= $maxDim->height,
			default => ($realDim->width >= $maxDim->width && $maxDim->width !== 0) || ($realDim->height >= $maxDim->height && $maxDim->height !== 0)
		};

		return $isLargeEnough ?
			$this->createSizeVariantInternal($sizeVariant, $maxDim) :
			null;
	}

	/**
	 * Generates the designated size variant unconditionally.
	 *
	 * The method does not check whether the size variant already exist
	 * and will overwrite an existing one of the same type.
	 *
	 * @param SizeVariantType $sizeVariant the desired size variant; admissible
	 *                                     values are:
	 *                                     {@link SizeVariantType::THUMB},
	 *                                     {@link SizeVariantType::THUMB2X},
	 *                                     {@link SizeVariantType::SMALL},
	 *                                     {@link SizeVariantType::SMALL2X},
	 *                                     {@link SizeVariantType::MEDIUM} and
	 *                                     {@link SizeVariantType::MEDIUM2X}
	 * @param ImageDimension  $maxDim      the designated dimensions of the
	 *                                     size variant
	 *
	 * @return SizeVariant the generated size variant
	 *
	 * @throws LycheeException
	 */
	private function createSizeVariantInternal(SizeVariantType $sizeVariant, ImageDimension $maxDim): SizeVariant
	{
		$svImage = match ($sizeVariant) {
			SizeVariantType::THUMB, SizeVariantType::THUMB2X => $this->referenceImage->cloneAndCrop($maxDim),
			default => $this->referenceImage->cloneAndScale($maxDim)
		};

		$svFile = $this->namingStrategy->createFile($sizeVariant);
		$svImage->save($svFile);

		return $this->photo->size_variants->create(
			$sizeVariant,
			$svFile->getRelativePath(),
			$svImage->getDimensions(),
			$svFile->getFilesize()
		);
	}

	/**
	 * Determines the maximum dimensions of the designated size variant.
	 *
	 * @param SizeVariantType $sizeVariant the size variant
	 *
	 * @return ImageDimension
	 *
	 * @throws InvalidSizeVariantException
	 */
	protected function getMaxDimensions(SizeVariantType $sizeVariant): ImageDimension
	{
		switch ($sizeVariant) {
			case SizeVariantType::MEDIUM2X:
				$maxWidth = 2 * Configs::getValueAsInt('medium_max_width');
				$maxHeight = 2 * Configs::getValueAsInt('medium_max_height');
				break;
			case SizeVariantType::MEDIUM:
				$maxWidth = Configs::getValueAsInt('medium_max_width');
				$maxHeight = Configs::getValueAsInt('medium_max_height');
				break;
			case SizeVariantType::SMALL2X:
				$maxWidth = 2 * Configs::getValueAsInt('small_max_width');
				$maxHeight = 2 * Configs::getValueAsInt('small_max_height');
				break;
			case SizeVariantType::SMALL:
				$maxWidth = Configs::getValueAsInt('small_max_width');
				$maxHeight = Configs::getValueAsInt('small_max_height');
				break;
			case SizeVariantType::THUMB2X:
				$maxWidth = self::THUMBNAIL2X_DIM;
				$maxHeight = self::THUMBNAIL2X_DIM;
				break;
			case SizeVariantType::THUMB:
				$maxWidth = self::THUMBNAIL_DIM;
				$maxHeight = self::THUMBNAIL_DIM;
				break;
			default:
				throw new InvalidSizeVariantException('unknown size variant: ' . $sizeVariant->value);
		}

		return new ImageDimension($maxWidth, $maxHeight);
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
	 * @param SizeVariantType $sizeVariant the indicated size variant
	 *
	 * @return bool true, if the size variant is enabled and the allowed width
	 *              or height is unequal to zero
	 *
	 * @throws InvalidSizeVariantException
	 */
	protected function isEnabledByConfiguration(SizeVariantType $sizeVariant): bool
	{
		$maxDim = $this->getMaxDimensions($sizeVariant);
		if ($maxDim->width === 0 && $maxDim->height === 0) {
			return false;
		}

		return match ($sizeVariant) {
			SizeVariantType::MEDIUM2X => Configs::getValueAsBool('medium_2x'),
			SizeVariantType::SMALL2X => Configs::getValueAsBool('small_2x'),
			SizeVariantType::THUMB2X => Configs::getValueAsBool('thumb_2x'),
			SizeVariantType::SMALL, SizeVariantType::MEDIUM, SizeVariantType::THUMB => true,
			default => throw new InvalidSizeVariantException('unknown size variant: ' . $sizeVariant->value),
		};
	}
}
