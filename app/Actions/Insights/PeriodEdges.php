<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights;

use App\DTO\Insights\InsightsPeriod;
use Illuminate\Support\Facades\DB;

/**
 * The edge strips of a Year or Range: the capture times of the widened UTC
 * window outside the core window, whose local date must be checked in PHP
 * (Feature 108, NFR-108-02).
 */
final class PeriodEdges
{
	/**
	 * IDs of the photos in the edge strips (every owner, no scope), ascending,
	 * in pages of 1 to `$chunk`, so that a query bound to one page reads the
	 * rows of at most `$chunk` edge photos (NFR-108-04). Nothing for the
	 * Library period.
	 *
	 * Each page is a keyset over `photos.id` and binds five values.
	 *
	 * @return \Generator<int,string[]>
	 */
	public static function photoIdPages(InsightsPeriod $period, int $chunk): \Generator
	{
		$window = $period->utcWindow();
		$core = $period->utcCore();
		if ($window === null || $core === null) {
			return;
		}

		$last = '';
		do {
			$page = DB::table('photos')
				->where('photos.id', '>', $last)
				->whereBetween('photos.taken_at', $window)
				->whereNotBetween('photos.taken_at', $core)
				->orderBy('photos.id')
				->limit($chunk)
				->pluck('photos.id')
				->all();
			if ($page === []) {
				return;
			}
			$last = $page[count($page) - 1];

			yield $page;
		} while (count($page) === $chunk);
	}
}
