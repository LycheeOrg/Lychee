<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Constants\PhotoAlbum as PA;
use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Jobs\RecomputeAlbumStatsJob;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * RecomputeTakenAtRange - Maintenance controller for the albums whose
 * `min_taken_at`/`max_taken_at` do not match their photos.
 *
 * The range of every album is recomputed the way RecomputeAlbumStatsJob does
 * (`photos.taken_at` over the album and its descendants) and compared to the
 * second: `photos.taken_at` keeps microseconds, the album columns do not.
 *
 * FulfillPreCompute only picks albums whose computed fields are all empty;
 * this one also finds ranges that are set but out of date.
 * `php artisan lychee:recompute-album-stats` recomputes every album instead.
 *
 * Behavior Based on Queue Configuration:
 * - If queue is 'sync': Recomputes the first 100 albums in tree order
 * - If queue is NOT 'sync': Dispatches a job for every album found
 */
class RecomputeTakenAtRange extends Controller
{
	private const SYNC_CHUNK = 100;

	/**
	 * Dispatches RecomputeAlbumStatsJob for each album with a stale range.
	 *
	 * Every stale album is in the list, ancestors included, and an album's
	 * range is read from the photos, not from its children: no propagation.
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		$album_ids = $this->getAlbumIdsWithStaleRange();
		if (Config::get('queue.default', 'sync') === 'sync') {
			$album_ids = array_slice($album_ids, 0, self::SYNC_CHUNK);
		}

		foreach ($album_ids as $album_id) {
			RecomputeAlbumStatsJob::dispatch($album_id, false);
		}
	}

	/**
	 * Count the albums whose range does not match their photos.
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return int
	 */
	public function check(MaintenanceRequest $request): int
	{
		return count($this->getAlbumIdsWithStaleRange());
	}

	/**
	 * @return string[] ids of the albums in tree order
	 */
	private function getAlbumIdsWithStaleRange(): array
	{
		$ranges = DB::table('albums', 'a')
			->join('albums as d', fn (JoinClause $join) => $join
				->on('d._lft', '>=', 'a._lft')
				->on('d._lft', '<=', 'a._rgt')
				->on('d._rgt', '<=', 'a._rgt'))
			->join(PA::PHOTO_ALBUM, PA::ALBUM_ID, '=', 'd.id')
			->join('photos', 'photos.id', '=', PA::PHOTO_ID)
			->whereNotNull('photos.taken_at')
			->groupBy('a.id')
			->select('a.id')
			->selectRaw('MIN(photos.taken_at) as min_taken_at, MAX(photos.taken_at) as max_taken_at');

		$rows = DB::table('albums')
			->leftJoinSub($ranges, 'ranges', 'ranges.id', '=', 'albums.id')
			->select([
				'albums.id',
				'albums.min_taken_at',
				'albums.max_taken_at',
				'ranges.min_taken_at as photos_min_taken_at',
				'ranges.max_taken_at as photos_max_taken_at',
			])
			->orderBy('albums._lft')
			->cursor();

		$album_ids = [];
		foreach ($rows as $row) {
			if (self::isStale($row)) {
				$album_ids[] = $row->id;
			}
		}

		return $album_ids;
	}

	private static function isStale(object $row): bool
	{
		return self::toSecond($row->min_taken_at) !== self::toSecond($row->photos_min_taken_at) ||
			self::toSecond($row->max_taken_at) !== self::toSecond($row->photos_max_taken_at);
	}

	private static function toSecond(?string $datetime): ?string
	{
		return $datetime === null ? null : substr($datetime, 0, 19);
	}
}
