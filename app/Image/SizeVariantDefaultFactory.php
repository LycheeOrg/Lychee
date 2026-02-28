<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Image;

use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\Models\SizeVariantFactory;
use App\DTO\CreateSizeVariantFlags;
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
	protected ImageHandlerInterface $reference_image;
	protected ?Photo $photo = null;
	protected ?AbstractSizeVariantNamingStrategy $naming_strategy = null;
	protected ?SizeVariantDimensionHelpers $sv_dimension_helpers = null;

	/**
	 * {@inheritDoc}
	 */
	public function init(Photo $photo, ?ImageHandlerInterface $reference_image = null, ?AbstractSizeVariantNamingStrategy $naming_strategy = null): void
	{
		$this->sv_dimension_helpers = new SizeVariantDimensionHelpers();

		try {
			$this->photo = $photo;
			if ($reference_image !== null && $reference_image->isLoaded()) {
				$this->reference_image = $reference_image;
			} else {
				$this->loadReferenceImage();
			}
			$this->naming_strategy = $naming_strategy ?? resolve(AbstractSizeVariantNamingStrategy::class);
			// Ensure that the naming strategy is linked to this photo
			$this->naming_strategy->setPhoto($this->photo);
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
		$original_file = $this->photo->size_variants->getOriginal()->getFile();

		if (!$this->photo->isVideo()) {
			$this->reference_image = resolve(ImageHandler::class);
			$this->reference_image->load($original_file);
		} else {
			if ($original_file->isLocalFile()) {
				// If the original size variant is hosted locally,
				// we can directly take it as a source file
				$source_file = $original_file->toLocalFile();
			} else {
				// If the original size variant is hosted remotely,
				// we must download it first; we exploit the temporary file
				// for that
				$source_file = new TemporaryLocalFile($original_file->getOriginalExtension(), $this->photo->title);
				$source_file->write($original_file->read(), false, $this->photo->type);
			}

			$video_handler = new VideoHandler();
			$video_handler->load($source_file);
			$position = is_numeric($this->photo->aperture) ? floatval($this->photo->aperture) / 2 : 0.0;
			$this->reference_image = $video_handler->extractFrame($position);

			// Clean up
			if ($source_file instanceof TemporaryLocalFile) {
				$source_file->delete();
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function createSizeVariants(): Collection
	{
		$all_variants = [
			SizeVariantType::PLACEHOLDER,
			SizeVariantType::THUMB,
			SizeVariantType::THUMB2X,
			SizeVariantType::SMALL,
			SizeVariantType::SMALL2X,
			SizeVariantType::MEDIUM,
			SizeVariantType::MEDIUM2X,
		];
		$collection = new Collection();

		foreach ($all_variants as $variant) {
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
	public function createSizeVariantCond(SizeVariantType $size_variant): ?SizeVariant
	{
		if ($size_variant === SizeVariantType::ORIGINAL || $size_variant === SizeVariantType::RAW) {
			throw new InvalidSizeVariantException('createSizeVariantCond() must not be used to create original or raw size');
		}
		if (!$this->sv_dimension_helpers->isEnabledByConfiguration($size_variant)) {
			return null;
		}
		// Don't generate medium size variants for videos, because the current web front-end has no use for it. Let's save some storage space.
		if ($this->photo->isVideo() && ($size_variant === SizeVariantType::MEDIUM || $size_variant === SizeVariantType::MEDIUM2X)) {
			return null;
		}
		// Don't re-create existing size variant
		if ($this->photo->size_variants->getSizeVariant($size_variant) !== null) {
			return null;
		}

		$max_dim = $this->sv_dimension_helpers->getMaxDimensions($size_variant);
		$real_dim = $this->reference_image->getDimensions();

		return $this->sv_dimension_helpers->isLargeEnough($real_dim, $max_dim, $size_variant) ?
			$this->createSizeVariantInternal($size_variant, $max_dim) :
			null;
	}

	/**
	 * Generates the designated size variant unconditionally.
	 *
	 * The method does not check whether the size variant already exist
	 * and will overwrite an existing one of the same type.
	 *
	 * @param SizeVariantType $size_variant the desired size variant; admissible
	 *                                      values are:
	 *                                      {@link SizeVariantType::THUMB},
	 *                                      {@link SizeVariantType::THUMB2X},
	 *                                      {@link SizeVariantType::SMALL},
	 *                                      {@link SizeVariantType::SMALL2X},
	 *                                      {@link SizeVariantType::MEDIUM} and
	 *                                      {@link SizeVariantType::MEDIUM2X}
	 * @param ImageDimension  $max_dim      the designated dimensions of the size variant
	 *
	 * @return SizeVariant the generated size variant
	 *
	 * @throws LycheeException
	 */
	private function createSizeVariantInternal(SizeVariantType $size_variant, ImageDimension $max_dim): SizeVariant
	{
		$sv_image = match ($size_variant) {
			SizeVariantType::THUMB, SizeVariantType::THUMB2X, SizeVariantType::PLACEHOLDER => $this->reference_image->cloneAndCrop($max_dim),
			default => $this->reference_image->cloneAndScale($max_dim),
		};

		$sv_file = $this->naming_strategy->createFile(
			$size_variant,
			new CreateSizeVariantFlags(disk: $this->photo->size_variants->getOriginal()->storage_disk));
		$sv_image->save($sv_file);

		return $this->photo->size_variants->create(
			$size_variant,
			$sv_file->getRelativePath(),
			$sv_image->getDimensions(),
			$sv_file->getFilesize()
		);
	}
}
