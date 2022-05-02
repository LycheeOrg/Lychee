<?php

namespace App\Image;

use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\DTO\ImageDimension;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Exceptions\ModelDBException;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
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
	public function init(Photo $photo, SizeVariantNamingStrategy $namingStrategy, ImageHandlerInterface $referenceImage): void
	{
		$this->photo = $photo;
		// if ($namingStrategy) {
		$this->namingStrategy = $namingStrategy;
		/*} elseif (!$this->namingStrategy) {
			$this->namingStrategy = resolve(SizeVariantNamingStrategy::class);
		}*/
		// Ensure that the naming strategy is linked to this photo
		$this->namingStrategy->setPhoto($this->photo);
		// if ($referenceImage) {
		$this->referenceImage = $referenceImage;
		/*} else {
			$this->referenceImage = resolve(ImageHandlerInterface::class);
		}*/
	}

	/**
	 * TODO: If we can assume that the reference image is always loaded, then we can remove this method.
	 *
	 * @throws ExternalComponentMissingException
	 * @throws ConfigurationException
	 * @throws MediaFileOperationException
	 * @throws IllegalOrderOfOperationException
	 * @throws MediaFileUnsupportedException
	 */
	protected function loadReferenceImage(): void
	{
		if ($this->referenceImage->isLoaded()) {
			$this->referenceImage->reset();
		}

		$originalFile = $this->photo->size_variants->getOriginal()->getFile();

		if (!$this->photo->isVideo()) {
			$this->referenceImage->load($originalFile);
		} else {
			if ($originalFile->toLocalFile()) {
				// If the original size variant is hosted locally,
				// we can directly take it as a source file
				$sourceFile = $originalFile->toLocalFile();
			} else {
				// If the original size variant is hosted remotely,
				// we must download it first we exploit the temporary file
				// for that
				$sourceFile = new TemporaryLocalFile($originalFile->getOriginalExtension(), $this->photo->title);
				$sourceFile->write($originalFile->read(), $this->photo->type);
			}

			$videoHandler = new VideoHandler();
			$videoHandler->load($sourceFile);

			// A temporary, local file for the extracted frame
			$frameFile = new TemporaryLocalFile('.jpg', $this->photo->title);
			$position = empty($this->photo->aperture) ? 0.0 : floatval($this->photo->aperture) / 2;
			$videoHandler->saveFrame($frameFile, $position);

			// Load the reference image into the image handler
			$this->referenceImage->load($frameFile);

			// Clean up
			if ($sourceFile instanceof TemporaryLocalFile) {
				$sourceFile->delete();
			}
			$frameFile->delete();
		}
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
			throw new InvalidSizeVariantException('createSizeVariant() must not be used to create original size');
		}
		if (!$this->referenceImage->isLoaded()) {
			$this->loadReferenceImage();
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
			throw new InvalidSizeVariantException('createSizeVariantCond() must not be used to create original size');
		}
		if (!$this->isEnabledByConfiguration($sizeVariant)) {
			return null;
		}
		// Don't generate medium size variants for videos, because the current web front-end has no use for it. Let's save some storage space.
		if ($this->photo->isVideo() && ($sizeVariant === SizeVariant::MEDIUM || $sizeVariant === SizeVariant::MEDIUM2X)) {
			return null;
		}
		if (!$this->referenceImage->isLoaded()) {
			$this->loadReferenceImage();
		}

		$maxDim = $this->getMaxDimensions($sizeVariant);
		$realDim = $this->referenceImage->getDimensions();

		if ($sizeVariant === SizeVariant::THUMB) {
			$isLargeEnough = true;
		} elseif ($sizeVariant === SizeVariant::THUMB2X) {
			$isLargeEnough = $realDim->width > $maxDim->width && $realDim->height > $maxDim->height;
		} else {
			$isLargeEnough = $realDim->width > $maxDim->width || $realDim->height > $maxDim->height;
		}

		return $isLargeEnough ?
			$this->createSizeVariantInternal($sizeVariant, $maxDim) :
			null;
	}

	/**
	 * @throws ModelDBException
	 * @throws IllegalOrderOfOperationException
	 * @throws MediaFileOperationException
	 * @throws InvalidSizeVariantException
	 * @throws ImageProcessingException
	 * @throws LycheeDomainException
	 */
	protected function createSizeVariantInternal(int $sizeVariant, ImageDimension $maxDim): SizeVariant
	{
		if ($this->photo->size_variants->getSizeVariant($sizeVariant)) {
			throw new InvalidSizeVariantException('size variant already exists');
		}

		$svImage = clone $this->referenceImage;
		if ($sizeVariant === SizeVariant::THUMB || $sizeVariant === SizeVariant::THUMB2X) {
			$svImage->crop($maxDim);
			$realDim = $maxDim;
		} else {
			$realDim = $svImage->scale($maxDim);
		}

		$svFile = $this->namingStrategy->createFile($sizeVariant);
		$svImage->save($svFile);

		return $this->photo->size_variants->create(
			$sizeVariant,
			$svFile->getRelativePath(),
			$realDim,
			$svFile->getFilesize()
		);
	}

	/**
	 * Determines the maximum dimensions of the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return ImageDimension
	 *
	 * @throws InvalidSizeVariantException
	 */
	protected function getMaxDimensions(int $sizeVariant): ImageDimension
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
}
