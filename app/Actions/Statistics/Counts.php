<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Statistics;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * We compute the size usage from the size variants.
 * Do note that this number may be slightly off due to the way we store pictures in the database:
 * row are duplicates for pictures, but the file is stored only once.
 */
final class Counts
{
	/**
	 * Return the number of photos uploaded per day over a period of time.
	 *
	 * @param int|null $owner_id
	 * @param int      $min_date number of days in the past to start counting from today
	 * @param int      $max_date number of days in the past to stop counting from today
	 *
	 * @return Collection<int,object{date:string,uploads:int}>
	 */
	public function getCreatedAtCountOverTime(?int $owner_id = null, int $min_date = 365, int $max_date = 0): Collection
	{
		$max_date_object = Carbon::now()->subDays($max_date);
		$min_date_object = Carbon::now()->subDays($min_date);

		/** @var Collection<int,object{date:string,uploads:int}> */
		return DB::table('photos')->selectRaw(' DATE(created_at) date, count(*) uploads')
			->where('created_at', '>', $min_date_object)
			->where('created_at', '<=', $max_date_object)
			->when($owner_id !== null, function ($query, $owner_id) {
				return $query->where('owner_id', $owner_id);
			})
			->groupBy('date')
			->orderBy('date', 'asc')
			->get();
	}

	/**
	 * Return the number of photos taken per day over a period of time.
	 *
	 * @param int|null $owner_id
	 * @param int      $min_date number of days in the past to start counting from today
	 * @param int      $max_date number of days in the past to stop counting from today
	 *
	 * @return Collection<int,object{date:string,uploads:int}>
	 */
	public function getTakenAtCountOverTime(?int $owner_id = null, int $min_date = 365, int $max_date = 0): Collection
	{
		$max_date_object = Carbon::now()->subDays($max_date);
		$min_date_object = Carbon::now()->subDays($min_date);

		/** @var Collection<int,object{date:string,uploads:int}> */
		return DB::table('photos')->selectRaw(' DATE(taken_at) date, count(*) uploads')
			->where('taken_at', '>', $min_date_object)
			->where('taken_at', '<=', $max_date_object)
			->when($owner_id !== null, function ($query, $owner_id) {
				return $query->where('owner_id', $owner_id);
			})
			->groupBy('date')
			->orderBy('date', 'asc')
			->get();
	}

	/**
	 * Return the minimum created_at date of all photos for a user.
	 *
	 * @param int|null $owner_id
	 *
	 * @return string
	 */
	public function getMinCreatedAt(?int $owner_id = null): string
	{
		return DB::table('photos')
			->select(DB::raw('min(created_at) as min_created_at'))
			->when($owner_id !== null, function ($query, $owner_id) {
				return $query->where('owner_id', $owner_id);
			})
			->first()?->min_created_at ?? '';
	}

	/**
	 * Return the minimum taken_at date of all photos for a user.
	 *
	 * @param int|null $owner_id
	 *
	 * @return string
	 */
	public function getMinTakenAt(?int $owner_id = null): string
	{
		return DB::table('photos')
			->select(DB::raw('min(taken_at) as min_taken_at'))
			->whereNotNull('taken_at')
			->when($owner_id !== null, function ($query, $owner_id) {
				return $query->where('owner_id', $owner_id);
			})
			->first()?->min_taken_at ?? '';
	}
}