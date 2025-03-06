<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Eloquent\FixedQueryBuilder;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Timeline
{
	protected PhotoQueryPolicy $photoQueryPolicy;

	public function __construct(PhotoQueryPolicy $photoQueryPolicy)
	{
		$this->photoQueryPolicy = $photoQueryPolicy;
	}

	/**
	 * Create the query manually.
	 *
	 * @return FixedQueryBuilder<Photo>
	 */
	public function do(): Builder
	{
		$order = Configs::getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);

		// Safe default (should not be needed).
		// @codeCoverageIgnoreStart
		if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
			$order = ColumnSortingPhotoType::TAKEN_AT;
		}
		// @codeCoverageIgnoreEnd

		return $this->photoQueryPolicy->applySearchabilityFilter(
			query: Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->orderBy($order->value, OrderSortingType::DESC->value);
	}

	/**
	 * Return the number of pictures that are younger than this.
	 * We use this to dertermine the current page given a date.
	 *
	 * @param Carbon $date
	 *
	 * @return int
	 */
	public function countYounger(Carbon $date): int
	{
		$order = Configs::getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);

		// Safe default (should not be needed).
		// @codeCoverageIgnoreStart
		if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
			$order = ColumnSortingPhotoType::TAKEN_AT;
		}
		// @codeCoverageIgnoreEnd

		return $this->photoQueryPolicy->applySearchabilityFilter(
			query: Photo::query()
				->where($order->value, '>', $date->format('Y-m-d H:i:s'))
				->whereNotNull($order->value),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->count();
	}

	/**
	 * Get all the dates of the timeline.
	 *
	 * @return array
	 */
	public function dates(): array
	{
		$order = Configs::getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);

		// Safe default (should not be needed).
		// @codeCoverageIgnoreStart
		if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
			$order = ColumnSortingPhotoType::TAKEN_AT;
		}
		// @codeCoverageIgnoreEnd

		return $this->photoQueryPolicy->applySearchabilityFilter(
			query: Photo::query()
				->selectRaw('DATE_FORMAT(' . $order->value . ', "%Y-%m-%d") as date')
				->whereNotNull($order->value),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->groupBy('date')
			->orderBy('date', OrderSortingType::DESC->value)
			->pluck('date')->all();
	}
}
