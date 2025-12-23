<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image;

use App\DTO\ImageDimension;
use App\Enum\ShiftType;
use App\Enum\ShiftX;
use App\Enum\ShiftY;
use App\Enum\WatermarkPosition;
use App\Repositories\ConfigManager;

/**
 * CoordinateCalculator class for handling watermark positioning and transformations.
 *
 * This class calculates the precise placement coordinates, scaling, opacity,
 * and shift adjustments for watermarks that are applied to images.
 */
class CoordinateCalculator
{
	public function __construct(
		private ConfigManager $config_manager)
	{
	}

	/**
	 * Applies scaling to the watermark image based on configured watermark size.
	 *
	 * Retrieves the watermark_size configuration value, converts it to a percentage,
	 * and scales the watermark dimensions proportionally.
	 *
	 * @param ImageDimension $dimentions Original dimensions of the watermark image
	 *
	 * @return ImageDimension New dimensions after scaling
	 */
	public function apply_scaling(ImageDimension $dimentions): ImageDimension
	{
		$val = $this->config_manager->getValueAsInt('watermark_size');
		$val = $this->to_percent($val, 1);

		return new ImageDimension(intval($dimentions->width * $val), intval($dimentions->height * $val));
	}

	/**
	 * Retrieves the opacity value for the watermark from configuration.
	 *
	 * Gets the watermark_opacity configuration value and converts it
	 * to a percentage for applying transparency to the watermark.
	 *
	 * @return float Opacity value as a percentage (0.0-1.0)
	 */
	public function get_opacity(): float
	{
		$val = $this->config_manager->getValueAsInt('watermark_opacity');

		return $this->to_percent($val, 1);
	}

	/**
	 * Calculate the coordinates for the position of the watermark.
	 *
	 * Based on the configured watermark position (top-left, center, bottom-right, etc.),
	 * this method calculates the exact x,y coordinates for placing the watermark on the image.
	 * The position is calculated relative to the top-left corner of the base image.
	 *
	 * @param ImageDimension $dimensions_img       Dimension of the image on which we apply the watermark
	 * @param ImageDimension $dimensions_watermark Dimension of the watermark (after scaling)
	 *
	 * @return ImageDimension Position coordinates from the top left corner of the image
	 */
	public function get_coordinates(ImageDimension $dimensions_img, ImageDimension $dimensions_watermark): ImageDimension
	{
		$val = $this->config_manager->getValueAsEnum('watermark_position', WatermarkPosition::class);

		$x = match ($val) {
			WatermarkPosition::TOP_LEFT, WatermarkPosition::LEFT, WatermarkPosition::BOTTOM_LEFT => 0,
			WatermarkPosition::TOP, WatermarkPosition::CENTER, WatermarkPosition::BOTTOM => ($dimensions_img->width - $dimensions_watermark->width) / 2,
			WatermarkPosition::TOP_RIGHT, WatermarkPosition::RIGHT, WatermarkPosition::BOTTOM_RIGHT => ($dimensions_img->width - $dimensions_watermark->width),
			default => 0,
		};

		$y = match ($val) {
			WatermarkPosition::TOP_LEFT, WatermarkPosition::TOP, WatermarkPosition::TOP_RIGHT => 0,
			WatermarkPosition::LEFT, WatermarkPosition::CENTER, WatermarkPosition::RIGHT => ($dimensions_img->height - $dimensions_watermark->height) / 2,
			WatermarkPosition::BOTTOM_LEFT, WatermarkPosition::BOTTOM, WatermarkPosition::BOTTOM_RIGHT => ($dimensions_img->height - $dimensions_watermark->height),
			default => 0,
		};

		return new ImageDimension($x, $y);
	}

	/**
	 * Apply additional shift on the coordinates of the watermark.
	 *
	 * Applies a configured fine-tuning shift to the watermark position after the initial
	 * placement. The shift can be either absolute (in pixels) or relative (as a percentage
	 * of the image dimensions), and can be applied in any direction (left/right, up/down).
	 * This method also ensures the watermark stays within the image boundaries.
	 *
	 * @param ImageDimension $dimensions_img Dimension of the image on which we apply the watermark
	 * @param ImageDimension $coordinates    Initial coordinates of the watermark
	 *
	 * @return ImageDimension Final coordinates after applying the shift
	 */
	public function apply_shift(ImageDimension $dimensions_img, ImageDimension $coordinates): ImageDimension
	{
		$shift_type = $this->config_manager->getValueAsEnum('watermark_shift_type', ShiftType::class);
		$x_direction = $this->config_manager->getValueAsEnum('watermark_shift_x_direction', ShiftX::class) === ShiftX::LEFT ? -1 : 1;
		$y_direction = $this->config_manager->getValueAsEnum('watermark_shift_y_direction', ShiftY::class) === ShiftY::UP ? -1 : 1;
		$x_shift = $this->config_manager->getValueAsInt('watermark_shift_x');
		$y_shift = $this->config_manager->getValueAsInt('watermark_shift_y');

		if ($shift_type === ShiftType::RELATIVE) {
			$x_percent = $this->to_percent($x_shift);
			$y_percent = $this->to_percent($y_shift);
			$x_shift = $x_percent * $dimensions_img->width;
			$y_shift = $y_percent * $dimensions_img->height;
		}

		$new_x = $coordinates->width + ($x_direction * $x_shift);
		$new_y = $coordinates->height + ($y_direction * $y_shift);

		// Ensure watermark stays within image bounds
		$new_x = max(0, min($new_x, $dimensions_img->width));
		$new_y = max(0, min($new_y, $dimensions_img->height));

		return new ImageDimension(
			width: $new_x,
			height: $new_y
		);
	}

	/**
	 * Converts an integer to a percentage float value with boundary checking.
	 *
	 * Given an integer, bounds it between $min_val and $max_val before converting
	 * it into its percent float value (divided by 100). This utility method is used
	 * for converting configuration values to usable percentages for scaling, opacity,
	 * and positioning calculations.
	 *
	 * @param int $value   Value to convert
	 * @param int $min_val Minimum value allowed: default 0
	 * @param int $max_val Maximum value allowed: default 100
	 *
	 * @return float Bounded percentage value (0.0-1.0 by default)
	 */
	public function to_percent(int $value, int $min_val = 0, int $max_val = 100): float
	{
		$value = min($value, $max_val);
		$value = max($value, $min_val);

		return floatval($value) / 100.0;
	}
}