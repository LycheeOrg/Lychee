<?php

namespace App\Contracts;

use App\Image\ImageHandlerInterface;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Support\Collection;

abstract class SizeVariantFactory
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
	 * @param Photo                          $photo
	 * @param ImageHandlerInterface|null     $referenceImage
	 * @param SizeVariantNamingStrategy|null $namingStrategy
	 *
	 * @throws LycheeException
	 */
	abstract public function init(Photo $photo, ?ImageHandlerInterface $referenceImage = null, ?SizeVariantNamingStrategy $namingStrategy = null): void;

	/**
	 * Creates a size variant for the designated size variant.
	 *
	 * The returned entity is already persisted to DB and associated to the
	 * {@link Photo} object which has been passed to the most recent call
	 * of {@link SizeVariantFactory::init()}.
	 * The caller of the method _does not need_ to take care of correct
	 * foreign key relationships.
	 * Moreover, this method also creates the necessary "physical" image file.
	 * The caller of this method **must ensure** that the original size
	 * variant already exists and that the "physical" media file is already
	 * in place.
	 * Otherwise, this method won't be able to create the desired size variant.
	 * This method is inapt to create the original size variant.
	 * Use {@link SizeVariantFactory::createOriginal()} for that.
	 *
	 * @param int $sizeVariant the desired size variant; admissible values
	 *                         are:
	 *                         {@link SizeVariant::THUMB},
	 *                         {@link SizeVariant::THUMB2X},
	 *                         {@link SizeVariant::SMALL},
	 *                         {@link SizeVariant::SMALL2X},
	 *                         {@link SizeVariant::MEDIUM} and
	 *                         {@link SizeVariant::MEDIUM2X}
	 *
	 * @return SizeVariant the freshly created and persisted size variant
	 *
	 * @throws LycheeException
	 */
	abstract public function createSizeVariant(int $sizeVariant): SizeVariant;

	/**
	 * Conditionally creates a size variant for the designated size variant.
	 *
	 * Contrary to {@link SizeVariantFactory::createSizeVariant()} this method
	 * does not create a size variant of the desired type unconditionally,
	 * but based on a decision logic which is matter of the implementing
	 * concrete factory and may depend on application settings, supported
	 * file formats, the dimensions of the original media, etc.
	 *
	 * Otherwise, this method behaves identical to
	 * {@link SizeVariantFactory::createSizeVariant()}.
	 * Refer there for further information.
	 *
	 * @param int $sizeVariant the desired size variant; admissible values
	 *                         are:
	 *                         {@link SizeVariant::THUMB},
	 *                         {@link SizeVariant::THUMB2X},
	 *                         {@link SizeVariant::SMALL},
	 *                         {@link SizeVariant::SMALL2X},
	 *                         {@link SizeVariant::MEDIUM} and
	 *                         {@link SizeVariant::MEDIUM2X}
	 *
	 * @return SizeVariant|null the freshly created and persisted size variant
	 *
	 * @throws LycheeException
	 */
	abstract public function createSizeVariantCond(int $sizeVariant): ?SizeVariant;

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
	 * Otherwise, this method won't be able to create any size variant at all-
	 * This method is inapt to create the original size variant.
	 * Use {@link SizeVariantFactory::createOriginal()} for that.
	 *
	 * @return Collection the collection of created size variants
	 *
	 * @throws LycheeException
	 */
	abstract public function createSizeVariants(): Collection;
}
