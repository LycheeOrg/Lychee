<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every album gets its `album_size_statistics` row from the
 * RecomputeAlbumSizeJob dispatched when it is saved: a missing row means the
 * job never ran or failed, and the album is left out of the storage figures.
 */
class AlbumSizeStatisticsCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('album_size_statistics')) {
			return $next($data);
		}

		$missing = $this->count();
		if ($missing > 0) {
			$data[] = DiagnosticData::warn(sprintf('There are %d albums without size statistics.', $missing), self::class,
				['Go to the maintenance page to compute the album sizes, or run: php artisan lychee:recompute-album-sizes']);
		}

		return $next($data);
	}

	public function count(): int
	{
		return DB::table('albums')
			->leftJoin('album_size_statistics', 'albums.id', '=', 'album_size_statistics.album_id')
			->whereNull('album_size_statistics.album_id')
			->count();
	}
}
