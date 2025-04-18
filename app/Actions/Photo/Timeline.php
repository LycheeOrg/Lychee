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
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\TimelineGranularityException;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Timeline
{
	protected PhotoQueryPolicy $photo_query_policy;
	private TimelinePhotoGranularity $photo_granularity;

	public function __construct(PhotoQueryPolicy $photo_query_policy)
	{
		$this->photo_query_policy = $photo_query_policy;
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

		return $this->photo_query_policy->applySearchabilityFilter(
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

		return $this->photo_query_policy->applySearchabilityFilter(
			query: Photo::query()
				->where($order->value, '>', $date)
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

		return $this->photo_query_policy->applySearchabilityFilter(
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
	 * @return Collection<int,string>
	 */
	public function dates(): Collection
	{
		$order = Configs::getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);

		// Safe default (should not be needed).
		// @codeCoverageIgnoreStart
		if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
			$order = ColumnSortingPhotoType::TAKEN_AT;
		}
		// @codeCoverageIgnoreEnd

		// This is among the ugliest piece of code I had ever to write...
		$is_driver_pgsql = DB::getDriverName() === 'pgsql';

		$formatter = match (DB::getDriverName()) {
			'sqlite' => 'strftime("%2$s", %1$s)',
			'mysql' => 'DATE_FORMAT(%s, "%s")',
			'mariadb' => 'DATE_FORMAT(%s, "%s")',
			'pgsql' => "to_char(%s, '%s')",
			default => throw new LycheeInvalidArgumentException('Unsupported database driver'),
		};

		$date_format = match ($this->photo_granularity) {
			TimelinePhotoGranularity::YEAR => $is_driver_pgsql ? 'YYYY' : '%Y',
			TimelinePhotoGranularity::MONTH => $is_driver_pgsql ? 'YYYY-mm' : '%Y-%m',
			TimelinePhotoGranularity::DAY => $is_driver_pgsql ? 'YYYY-MM-DD' : '%Y-%m-%d',
			TimelinePhotoGranularity::HOUR => $is_driver_pgsql ? 'YYYY-MM-DD"T"HH24' : '%Y-%m-%dT%H', // hoepfully this is correct
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new TimelineGranularityException(),
		};

		return $this->photo_query_policy->applySearchabilityFilter(
			query: Photo::query()

				->selectRaw(sprintf($formatter, $order->value, $date_format) . ' as date')
				->whereNotNull($order->value),
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_timeline')
		)->groupBy('date')
			->orderBy('date', OrderSortingType::DESC->value)
			->pluck('date');
	}
}
