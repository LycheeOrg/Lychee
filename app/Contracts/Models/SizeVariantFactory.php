<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Models;

use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Image\ImageHandlerInterface;
use App\Enum\SizeVariantType;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Support\Collection;

interface SizeVariantFactory
{
	/**
	 * Initializes the factory and associates.
	 *
	 * This factory creates size variants for the passed {@link Photo} object
	 * with respect to the passed reference image and naming strategy.
	 * If `$namingStrategy` equals `null`, then the default naming
	 * strategy is used.
	 * If `$referenceImage` equals `null`, then a reference image is created
	 * from the photo's original size variant.
	 * This requires that the photo is already linked to an original size
	 * variant.
	 * Note, that this might be inefficient, if the media files are hosted
	 * remotely, because the reference image needs to be loaded.
	 * Hence, it is more efficient to pass a reference image, if a local
	 * copy is already available.
	 * However, there is no consistency check, if the provided reference
	 * image matches the original size variant of the photo.
	 *
	 * @param Photo                                  $photo
	 * @param ImageHandlerInterface|null             $referenceImage
	 * @param AbstractSizeVariantNamingStrategy|null $namingStrategy
	 *
	 * @throws LycheeException
	 */
	public function init(Photo $photo, ?ImageHandlerInterface $referenceImage = null, ?AbstractSizeVariantNamingStrategy $namingStrategy = null): void;

	/**
	 * Conditionally creates a size variant for the designated size variant.
	 *
	 * This method creates a size variant of the desired type based on a
	 * decision logic which is matter of the implementing
	 * concrete factory and may depend on application settings, supported
	 * file formats, the dimensions of the original media, etc.
	 *
	 * Otherwise, this method behaves identical to
	 * {@link SizeVariantFactory::createSizeVariant()}.
	 * Refer there for further information.
	 *
	 * @param SizeVariantType $sizeVariant the desired size variant; admissible values
	 *                                     are:
	 *                                     {@link SizeVariantType::THUMB},
	 *                                     {@link SizeVariantType::THUMB2X},
	 *                                     {@link SizeVariantType::SMALL},
	 *                                     {@link SizeVariantType::SMALL2X},
	 *                                     {@link SizeVariantType::MEDIUM} and
	 *                                     {@link SizeVariantType::MEDIUM2X}
	 *
	 * @return SizeVariant|null the freshly created and persisted size variant
	 *
	 * @throws LycheeException
	 */
	public function createSizeVariantCond(SizeVariantType $sizeVariant): ?SizeVariant;

	/**
	 * Creates a selected set of size variants.
	 *
	 * This method creates several size variants for the {@link Photo} object
	 * which has been passed to the most recent call of
	 * {@link SizeVariantFactory::init()}.
	 * Which specific size variants are created is matter of the implementing
	 * concrete factory and may depend on application settings, supported
	 * file formats, the dimensions of the original media, etc.
	 * The implementing factory is free to not create any size variant at all,
	 * but typically at least a thumbnail will be created.
	 * The caller of this method **must ensure** that the original size
	 * variant already exists and that the "physical" media file is already
	 * in place.
	 * Otherwise, this method won't be able to create any size variant at all.
	 * This method is inapt to create the original size variant.
	 * Use {@link SizeVariantFactory::createOriginal()} for that.
	 *
	 * @return Collection<int,SizeVariant> the collection of created size variants
	 *
	 * @throws LycheeException
	 */
	public function createSizeVariants(): Collection;
}
