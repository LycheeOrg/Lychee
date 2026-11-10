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
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `albums.min_taken_at`/`max_taken_at` are written by RecomputeAlbumStatsJob.
 * An album holding a photo with a taken date gets a range: a NULL there means
 * the job never ran for it, and its parent misplaces it when sorting its
 * children by taken date.
 *
 * Ranges that are set but out of date are counted on the maintenance page
 * (RecomputeTakenAtRange).
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

		$count = $this->count();
		if ($count > 0) {
			$data[] = DiagnosticData::warn(sprintf('There are %d albums with dated photos but no taken date range.', $count), self::class,
				['Go to the maintenance page to recompute them, or run: php artisan lychee:recompute-album-stats']);
		}

		return $next($data);
	}

	public function count(): int
	{
		return DB::table('albums')
			->where(fn (Builder $query) => $query->whereNull('albums.min_taken_at')->orWhereNull('albums.max_taken_at'))
			->whereExists(fn (Builder $query) => $query
				->selectRaw('1')
				->from(PA::PHOTO_ALBUM)
				->join('photos', 'photos.id', '=', PA::PHOTO_ID)
				->whereColumn(PA::ALBUM_ID, '=', 'albums.id')
				->whereNotNull('photos.taken_at'))
			->count();
	}
}
