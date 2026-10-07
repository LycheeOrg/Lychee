<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Accumulators;

use App\Actions\Insights\Helpers\AspectRatioClassifier;
use App\Actions\Insights\PhotoRow;
use App\Enum\AspectRatioGroup;
use App\Enum\ImageOrientation;
use App\Http\Resources\Insights\AspectRatioData;
use App\Http\Resources\Insights\DimensionsData;
use App\Http\Resources\Insights\FormatsData;
use App\Http\Resources\Insights\OrientationCountData;

/**
 * Orientation, aspect-ratio groups and pixel dimensions of the originals
 * (Feature 085, FR-085-17).
 *
 * Memory grows with the number of distinct dimensions, not with the number
 * of items.
 */
final class FormatAccumulator
{
	private const TOP_DIMENSIONS = 200;
	private const ALL = 'all';
	private const PHOTOS = 'photos';
	private const VIDEOS = 'videos';

	/** @var array<string,array<string,int>> media → orientation → items */
	private array $orientations = [];

	/** @var array<string,int> group → items */
	private array $groups = [];

	/** @var array<string,int> "width×height" → items */
	private array $dimensions = [];

	public function __construct()
	{
		foreach ([self::ALL, self::PHOTOS, self::VIDEOS] as $media) {
			$this->orientations[$media] = array_fill_keys(array_map(fn (ImageOrientation $o) => $o->value, ImageOrientation::cases()), 0);
		}
		$this->groups = array_fill_keys(array_map(fn (AspectRatioGroup $g) => $g->value, AspectRatioGroup::cases()), 0);
	}

	public function add(PhotoRow $row): void
	{
		$orientation = AspectRatioClassifier::orientation($row->width, $row->height)->value;
		$this->orientations[self::ALL][$orientation]++;
		$this->orientations[self::PHOTOS][$orientation] += intval($row->is_image);
		$this->orientations[self::VIDEOS][$orientation] += intval($row->is_video);

		$group = AspectRatioClassifier::group($row->width, $row->height);
		if ($group === null) {
			return;
		}

		$this->groups[$group->value]++;
		$key = $row->width . '×' . $row->height;
		$this->dimensions[$key] = ($this->dimensions[$key] ?? 0) + 1;
	}

	public function toFormats(): FormatsData
	{
		return new FormatsData(
			all: self::orientationData($this->orientations[self::ALL]),
			photos: self::orientationData($this->orientations[self::PHOTOS]),
			videos: self::orientationData($this->orientations[self::VIDEOS]),
			aspect_ratios: array_map(fn (AspectRatioGroup $g) => new AspectRatioData($g, $this->groups[$g->value]), AspectRatioGroup::cases()),
			dimensions: $this->dimensionsData(),
		);
	}

	/**
	 * @param array<string,int> $counts
	 */
	private static function orientationData(array $counts): OrientationCountData
	{
		return new OrientationCountData(
			portrait: $counts[ImageOrientation::PORTRAIT->value],
			landscape: $counts[ImageOrientation::LANDSCAPE->value],
			square: $counts[ImageOrientation::SQUARE->value],
			unknown: $counts[ImageOrientation::UNKNOWN->value],
		);
	}

	/**
	 * Most frequent first; equal counts by area, then width, both descending.
	 */
	private function dimensionsData(): DimensionsData
	{
		$entries = array_map(function (string $key, int $count): array {
			[$width, $height] = array_map(intval(...), explode('×', $key));

			return [$width, $height, $count];
		}, array_keys($this->dimensions), array_values($this->dimensions));
		usort($entries, fn (array $a, array $b) => [$b[2], $b[0] * $b[1], $b[0]] <=> [$a[2], $a[0] * $a[1], $a[0]]);
		$top = array_slice($entries, 0, self::TOP_DIMENSIONS);

		return new DimensionsData(
			widths: array_column($top, 0),
			heights: array_column($top, 1),
			counts: array_column($top, 2),
			formats: count($entries),
			with_dimensions: array_sum($this->dimensions),
		);
	}
}
