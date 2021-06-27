<?php

namespace App\Contracts;

use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Support\Collection;

abstract class SizeVariantFactory
{
	/**
	 * Initializes the factory and associates.
	 *
	 * This factory creates size variants for the passed {@link Photo} object
	 * with respect to the passed naming strategy.
	 * It is the caller's responsibility to also call
	 * {@link SizeVariantFactory::cleanup()} when the factory is not needed
	 * anymore.
	 * If `$namingStrategy` equals `null`, then the default naming
	 * strategy is used.
	 * Typically, this requires that the original size variant of `$photo`
	 * already exists such that the original size variant can be used to
	 * derive the names of further size variants.
	 *
	 * @param Photo                          $photo
	 * @param SizeVariantNamingStrategy|null $namingStrategy
	 */
	abstract public function init(Photo $photo, ?SizeVariantNamingStrategy $namingStrategy = null): void;

	/**
	 * Removes any temporary files which this factory may have created.
	 *
	 * The caller of this factory should call this method when this factory
	 * is not needed anymore.
	 */
	abstract public function cleanup(): void;

	/**
	 * Create a size variant usable for the original media file.
	 *
	 * The returned entity is already persisted to DB and associated to the
	 * {@link Photo} object which has been passed to the most recent call
	 * of {@link SizeVariantFactory::init()}.
	 * The caller of the method _does not need_ to take care of correct
	 * foreign key relationships.
	 * However, the caller of the method **must ensure** that there is
	 * actually a "physical" media file placed under the path to which the
	 * returned {@link SizeVariant} points to.
	 * But it is ok, to first call this method and then copy the physical file
	 * to its correct path.
	 * This method does not check, if a file actually exists.
	 *
	 * @param int $width  the width of the original size variant
	 * @param int $height the height of the original size variant
	 *
	 * @return SizeVariant the freshly created and persisted size variant
	 */
	abstract public function createOriginal(int $width, int $height): SizeVariant;

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
	 * Otherwise this methods behaves identical to
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
	 */
	abstract public function createSizeVariants(): Collection;
}