<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

use App\Enum\AspectRatioGroup;
use App\Enum\ImageOrientation;

/**
 * Orientation and aspect-ratio group of a pixel size (Feature 085, FR-085-17).
 *
 * A group matches when the long-to-short ratio is within 3 % of it, so that
 * 500 × 333 still reads as 3:2. Ratios of 2.2:1 and wider are panoramas.
 */
final class AspectRatioClassifier
{
	private const TOLERANCE = 0.03;
	private const PANORAMA_FROM = 2.2;

	public static function orientation(int $width, int $height): ImageOrientation
	{
		return match (true) {
			$width <= 0 || $height <= 0 => ImageOrientation::UNKNOWN,
			$width === $height => ImageOrientation::SQUARE,
			$width > $height => ImageOrientation::LANDSCAPE,
			default => ImageOrientation::PORTRAIT,
		};
	}

	public static function group(int $width, int $height): ?AspectRatioGroup
	{
		if ($width <= 0 || $height <= 0) {
			return null;
		}

		$ratio = max($width, $height) / min($width, $height);
		if ($ratio >= self::PANORAMA_FROM) {
			return AspectRatioGroup::PANORAMA;
		}

		foreach (AspectRatioGroup::cases() as $group) {
			$target = $group->ratio();
			if ($target !== null && abs($ratio - $target) / $target <= self::TOLERANCE) {
				return $group;
			}
		}

		return AspectRatioGroup::OTHER;
	}
}
