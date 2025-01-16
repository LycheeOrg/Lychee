<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image;

use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\Models\SizeVariantFactory;
use App\DTO\ImageDimension;
use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\Files\TemporaryLocalFile;
use App\Image\Handlers\ImageHandler;
use App\Image\Handlers\VideoHandler;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

class SizeVariantDefaultFactory implements SizeVariantFactory
{
	public const THUMBNAIL_DIM = 200;
	public const THUMBNAIL2X_DIM = 400;
	public const PLACEHOLDER_DIM = 16;

	/** @var ImageHandlerInterface the image handler (gd, imagick, ...) which is used to generate image files */
	protected ImageHandlerInterface $referenceImage;
	protected ?Photo $photo = null;
	protected ?AbstractSizeVariantNamingStrategy $namingStrategy = null;
	protected ?SizeVariantDimensionHelpers $svDimensionHelpers = null;

	/**
	 * {@inheritDoc}
	 */
	public function init(Photo $photo, ?ImageHandlerInterface $referenceImage = null, ?AbstractSizeVariantNamingStrategy $namingStrategy = null): void
	{
		$this->svDimensionHelpers = new SizeVariantDimensionHelpers();

		try {
			$this->photo = $photo;
			if ($referenceImage !== null && $referenceImage->isLoaded()) {
				$this->referenceImage = $referenceImage;
			} else {
				$this->loadReferenceImage();
			}
			$this->namingStrategy = $namingStrategy ?? resolve(AbstractSizeVariantNamingStrategy::class);
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
			SizeVariantType::PLACEHOLDER,
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
		if (!$this->svDimensionHelpers->isEnabledByConfiguration($sizeVariant)) {
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

		$maxDim = $this->svDimensionHelpers->getMaxDimensions($sizeVariant);
		$realDim = $this->referenceImage->getDimensions();

		return $this->svDimensionHelpers->isLargeEnough($realDim, $maxDim, $sizeVariant) ?
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
			SizeVariantType::THUMB, SizeVariantType::THUMB2X, SizeVariantType::PLACEHOLDER => $this->referenceImage->cloneAndCrop($maxDim),
			default => $this->referenceImage->cloneAndScale($maxDim),
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
}
