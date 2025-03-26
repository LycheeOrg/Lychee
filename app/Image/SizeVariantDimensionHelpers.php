<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image;

use App\DTO\ImageDimension;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Models\Configs;

class SizeVariantDimensionHelpers
{
	/**
	 * Determines the maximum dimensions of the designated size variant.
	 *
	 * @param SizeVariantType $size_variant the size variant
	 *
	 * @throws InvalidSizeVariantException
	 */
	public function getMaxDimensions(SizeVariantType $size_variant): ImageDimension
	{
		$max_width = $this->getMaxWidth($size_variant);
		$max_height = $this->getMaxHeight($size_variant);

		return new ImageDimension($max_width, $max_height);
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
	 * @param SizeVariantType $size_variant the indicated size variant
	 *
	 * @return bool true, if the size variant is enabled and the allowed width
	 *              or height is unequal to zero
	 *
	 * @throws InvalidSizeVariantException
	 */
	public function isEnabledByConfiguration(SizeVariantType $size_variant): bool
	{
		$max_dim = $this->getMaxDimensions($size_variant);
		if ($max_dim->width === 0 && $max_dim->height === 0) {
			return false;
		}

		return match ($size_variant) {
			SizeVariantType::MEDIUM2X => Configs::getValueAsBool('medium_2x'),
			SizeVariantType::SMALL2X => Configs::getValueAsBool('small_2x'),
			SizeVariantType::THUMB2X => Configs::getValueAsBool('thumb_2x'),
			SizeVariantType::PLACEHOLDER => Configs::getValueAsBool('low_quality_image_placeholder'),
			SizeVariantType::SMALL, SizeVariantType::MEDIUM, SizeVariantType::THUMB => true,
			default => throw new InvalidSizeVariantException('unknown size variant: ' . $size_variant->value),
		};
	}

	/**
	 * Given dimension and SizeVariant type, provide a check whether a SizeVariant should be created
	 * under the constraints provided.
	 *
	 * @param ImageDimension  $real_dim     the dimension of original
	 * @param ImageDimension  $max_dim      the max dimension of target size variant
	 * @param SizeVariantType $size_variant type of size variant to be created
	 *
	 * @return bool true, if the size is big enough for creation
	 */
	public function isLargeEnough(ImageDimension $real_dim, ImageDimension $max_dim, SizeVariantType $size_variant): bool
	{
		return match ($size_variant) {
			SizeVariantType::THUMB, SizeVariantType::PLACEHOLDER => true,
			SizeVariantType::THUMB2X => $real_dim->width >= $max_dim->width && $real_dim->height >= $max_dim->height,
			default => ($real_dim->width >= $max_dim->width && $max_dim->width !== 0) || ($real_dim->height >= $max_dim->height && $max_dim->height !== 0),
		};
	}

	/**
	 * Return the max width for the SizeVariant.
	 */
	public function getMaxWidth(SizeVariantType $size_variant): int
	{
		return match ($size_variant) {
			SizeVariantType::MEDIUM2X => 2 * Configs::getValueAsInt('medium_max_width'),
			SizeVariantType::MEDIUM => Configs::getValueAsInt('medium_max_width'),
			SizeVariantType::SMALL2X => 2 * Configs::getValueAsInt('small_max_width'),
			SizeVariantType::SMALL => Configs::getValueAsInt('small_max_width'),
			SizeVariantType::THUMB2X => SizeVariantDefaultFactory::THUMBNAIL2X_DIM,
			SizeVariantType::THUMB => SizeVariantDefaultFactory::THUMBNAIL_DIM,
			SizeVariantType::PLACEHOLDER => SizeVariantDefaultFactory::PLACEHOLDER_DIM,
			default => throw new InvalidSizeVariantException('No applicable for original'),
		};
	}

	/**
	 * Return the max height for the SizeVariant.
	 */
	public function getMaxHeight(SizeVariantType $size_variant): int
	{
		return match ($size_variant) {
			SizeVariantType::MEDIUM2X => 2 * Configs::getValueAsInt('medium_max_height'),
			SizeVariantType::MEDIUM => Configs::getValueAsInt('medium_max_height'),
			SizeVariantType::SMALL2X => 2 * Configs::getValueAsInt('small_max_height'),
			SizeVariantType::SMALL => Configs::getValueAsInt('small_max_height'),
			SizeVariantType::THUMB2X => SizeVariantDefaultFactory::THUMBNAIL2X_DIM,
			SizeVariantType::THUMB => SizeVariantDefaultFactory::THUMBNAIL_DIM,
			SizeVariantType::PLACEHOLDER => SizeVariantDefaultFactory::PLACEHOLDER_DIM,
			default => throw new InvalidSizeVariantException('unknown size variant: ' . $size_variant->value),
		};
	}
}