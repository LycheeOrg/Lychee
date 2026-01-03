<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Jobs\RecomputeAlbumSizeJob;
use App\Models\Album;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

/**
 * BackfillAlbumSizes - Maintenance controller for backfilling album size statistics.
 *
 * This controller is equivalent to running the artisan command:
 * php artisan lychee:backfill-album-sizes
 *
 * It processes albums that are missing size statistics and dispatches jobs
 * to compute storage size breakdowns by variant type.
 *
 * Behavior Based on Queue Configuration:
 * - If queue is 'sync': Processes albums in chunks of 50, ordered by _lft ASC
 *   (leaf-to-root order) to allow propagation to work correctly
 * - If queue is NOT 'sync': Dispatches all jobs at once, queue worker handles them
 *
 * Safe to run multiple times (idempotent). Already computed albums are skipped.
 *
 * Note that if we are using queues then this module can be used to force recomputing the statistics.
 */
class BackfillAlbumSizes extends Controller
{
	/**
	 * Execute the backfill operation.
	 *
	 * Dispatches RecomputeAlbumSizeJob for each album that needs size statistics.
	 * Job dispatch strategy depends on queue configuration:
	 * - Sync queue: Processes in chunks ordered by _lft ASC (leaf-to-root)
	 * - Async queue: Dispatches all jobs immediately
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		$is_sync = Config::get('queue.default', 'sync') === 'sync';
		$query = Album::query()
			->when($is_sync, fn ($q) => $q->whereDoesntHave('sizeStatistics'))
			->orderBy('_lft', 'asc'); // Leaf-to-root order for proper propagation

		if ($is_sync) {
			// For sync queue, process in chunks by _lft ASC (leaf to root)
			// This allows parent propagation to work correctly
			$albums = $query->limit(50)->toBase()->get(['id']);
			$albums->each(function ($album): void {
				RecomputeAlbumSizeJob::dispatch($album->id, false);
			});
		} else {
			// For async queue, dispatch all jobs at once
			// The queue worker will handle them
			$query->toBase()
				->select(['id'])
				->lazy(500)
				->each(function ($album): void {
					RecomputeAlbumSizeJob::dispatch($album->id, false);
				});
		}
	}

	/**
	 * Count albums that need size statistics filled.
	 *
	 * Returns the count of albums that don't have a corresponding
	 * row in the album_size_statistics table.
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return int Total number of albums needing size statistics
	 */
	public function check(MaintenanceRequest $request): int
	{
		if (Config::get('queue.default', 'sync') === 'sync') {
			return -1;
		}

		return Album::query()
			->whereDoesntHave('sizeStatistics')
			->count();
	}
}
