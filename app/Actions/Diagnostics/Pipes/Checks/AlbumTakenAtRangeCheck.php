<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `albums.min_taken_at`/`max_taken_at` hold the range of `photos.taken_at`
 * over the album and its descendants, written by RecomputeAlbumStatsJob.
 * A stale range misplaces the album when its parent sorts its children by
 * taken date.
 *
 * The range is recomputed the way the job does and compared to the second:
 * `photos.taken_at` keeps microseconds, the album columns do not.
 */
class AlbumTakenAtRangeCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasColumn('albums', 'max_taken_at')) {
			return $next($data);
		}

		$count = count($this->getAlbumIdsWithStaleRange());
		if ($count > 0) {
			$data[] = DiagnosticData::warn(sprintf('There are %d albums whose taken date range does not match their photos.', $count), self::class,
				['Albums sorted by taken date are misplaced. Run: php artisan lychee:recompute-album-stats']);
		}

		return $next($data);
	}

	/**
	 * @return string[] ids of the albums in tree order
	 */
	public function getAlbumIdsWithStaleRange(): array
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
