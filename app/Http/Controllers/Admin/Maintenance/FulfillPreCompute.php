<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

/**
 * FulfillPreCompute - Maintenance controller for backfilling album precomputed fields.
 *
 * This controller is equivalent to running the artisan command:
 * php artisan lychee:backfill-album-fields
 *
 * It processes albums where ALL precomputed fields are null and dispatches
 * jobs to compute:
 * - max_taken_at, min_taken_at (date range)
 * - num_children, num_photos (counts)
 * - auto_cover_id_max_privilege, auto_cover_id_least_privilege (cover IDs)
 *
 * Behavior Based on Queue Configuration:
 * - If queue is 'sync': Processes albums in chunks of 50, ordered by _lft DESC
 *   (root-to-leaf order) to avoid re-computation
 * - If queue is NOT 'sync': Dispatches all jobs at once, queue worker handles them
 *
 * Safe to run multiple times (idempotent). Already computed albums are skipped.
 */
class FulfillPreCompute extends Controller
{
	/**
	 * Execute the backfill operation.
	 *
	 * Dispatches RecomputeAlbumStatsJob for each album that needs computation.
	 * Job dispatch strategy depends on queue configuration:
	 * - Sync queue: Processes in chunks ordered by _lft DESC
	 * - Async queue: Dispatches all jobs immediately
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		$queue_connection = Config::get('queue.default', 'sync');
		$is_sync = $queue_connection === 'sync';

		$query = $this->getAlbumsNeedingComputation()
			->whereRaw("_lft = _rgt - 1") // Only leaf albums
			->orderBy('_lft', 'desc');

		if ($is_sync) {
			// For sync queue, process in chunks by _lft DESC (leaf to root)
			// This reduces re-computation as parents are processed before children
			$albums = $query->limit(50)->toBase()->get(['id']);
			$albums->each(function ($album): void {
				RecomputeAlbumStatsJob::dispatch($album->id);
			});
		} else {
			// For async queue, dispatch all jobs at once
			// The queue worker will handle them
			$query->toBase()
				->select(['id'])
				->lazy(500)
				->each(function ($album): void {
					RecomputeAlbumStatsJob::dispatch($album->id);
				});
		}
	}

	/**
	 * Count albums that need precomputed fields filled.
	 *
	 * Returns the count of albums where ALL precomputed fields are null:
	 * - max_taken_at IS NULL
	 * - min_taken_at IS NULL
	 * - num_children = 0 (default value indicates not computed)
	 * - num_photos = 0 (default value indicates not computed)
	 * - auto_cover_id_max_privilege IS NULL
	 * - auto_cover_id_least_privilege IS NULL
	 *
	 * Note: Empty albums legitimately have these values, but this check
	 * assumes that most albums have at least some content.
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return int Total number of albums needing computation
	 */
	public function check(MaintenanceRequest $request): int
	{
		return $this->getAlbumsNeedingComputation()->count();
	}

	/**
	 * Build query for albums needing computation.
	 *
	 * Selects albums where ALL precomputed fields are in their default/null state.
	 *
	 * @return Builder<Album>
	 */
	private function getAlbumsNeedingComputation(): Builder
	{
		return Album::query()
			->whereNull('max_taken_at')
			->whereNull('min_taken_at')
			->where('num_children', 0)
			->where('num_photos', 0)
			->whereNull('auto_cover_id_max_privilege')
			->whereNull('auto_cover_id_least_privilege');
	}
}
