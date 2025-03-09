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
use App\Enum\TimelinePhotoGranularity;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Timeline
{
	protected PhotoQueryPolicy $photoQueryPolicy;
	private TimelinePhotoGranularity $photo_granularity;

	public function __construct(PhotoQueryPolicy $photoQueryPolicy)
	{
		$this->photoQueryPolicy = $photoQueryPolicy;
		$this->photo_granularity = Configs::getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
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
	public function countYoungerFromDate(Carbon $date): int
	{
		$order = Configs::getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);

		// Safe default (should not be needed).
		// @codeCoverageIgnoreStart
		if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
			$order = ColumnSortingPhotoType::TAKEN_AT;
		}
		// @codeCoverageIgnoreEnd

		$date_format = $this->getDateFormat(false);

		return $this->photoQueryPolicy->applySearchabilityFilter(
			query: Photo::query()
				->where($order->value, '>', $date->format($date_format))
				->whereNotNull($order->value),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->count();
	}

	/**
	 * Return the number of pictures that are younger than this.
	 * We use this to dertermine the current page given a photo.
	 *
	 * @param Photo $photo
	 *
	 * @return int
	 */
	public function countYoungerFromPhoto(Photo $photo): int
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
				->joinSub(
					query: Photo::query()->select($order->value)->where('id', $photo->id),
					as: 'sub',
					first: 'sub.' . $order->value,
					operator: '<',
					second: 'photos.' . $order->value
				)
				->whereNotNull('photos.' . $order->value),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->count();
	}

	/**
	 * Get all the dates of the timeline.
	 *
	 * @return string[]
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

		$date_format = $this->getDateFormat(true);

		return $this->photoQueryPolicy->applySearchabilityFilter(
			query: Photo::query()
				->selectRaw('DATE_FORMAT(' . $order->value . ', "' . $date_format . '") as date')
				->whereNotNull($order->value),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->groupBy('date')
			->orderBy('date', OrderSortingType::DESC->value)
			->pluck('date')->all();
	}

	public function getDateFormat(bool $is_sql): string
	{
		$p = $is_sql ? '%' : '';

		return match ($this->photo_granularity) {
			TimelinePhotoGranularity::YEAR => $p . 'Y',
			TimelinePhotoGranularity::MONTH => $p . 'Y-' . $p . 'm',
			TimelinePhotoGranularity::DAY => $p . 'Y-' . $p . 'm-' . $p . 'd',
			TimelinePhotoGranularity::HOUR => $p . 'Y-' . $p . 'm-' . $p . 'd ' . $p . 'H:00',
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeLogicException('default & disabled are not a valid granularity for photos'),
		};
	}
}
