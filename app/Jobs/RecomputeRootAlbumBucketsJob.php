<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\DTO\AlbumSortingCriterion;
use App\Events\AlbumChildrenChanged;
use App\Models\Album;
use App\Services\AlbumBucketComputer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Recomputes `bucket_id` for every **root** album — closes the dispatch gap
 * {@see RecomputeChildAlbumBucketsJob} deliberately doesn't cover
 * (parent-scoped only): root albums have no parent to govern their
 * bucketing, so the instance-wide
 * `sorting_albums_col`/`sorting_albums_order`/`timeline_albums_granularity`/
 * `title_bucket_mode`/`title_bucket_prefix_length` config plays the role a
 * parent's own settings would for a sub-album.
 *
 * Only ever affects `own`-scope buckets — `shared` scope is always computed
 * live via a `GROUP BY owner_id` and needs no recompute path.
 *
 * Performs exactly one `SELECT` (raw rows, no Eloquent hydration) and one
 * bulk `upsert()`, mirroring
 * {@see RecomputeChildAlbumBucketsJob}'s shape exactly.
 */
class RecomputeRootAlbumBucketsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public $tries = 3;

	public function handle(): void
	{
		$sorting = AlbumSortingCriterion::createDefault();
		$bucket_computer = resolve(AlbumBucketComputer::class);
		$granularity = $bucket_computer->resolveGranularity(null);

		$rows = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->whereNull('albums.parent_id')
			->select([
				'albums.id',
				'base_albums.title',
				'base_albums.title_base',
				'base_albums.created_at',
				'albums.min_taken_at',
				'albums.max_taken_at',
			])
			->get();

		if ($rows->isEmpty()) {
			return;
		}

		$updates = [];
		foreach ($rows as $row) {
			$updates[] = [
				'id' => $row->id,
				'bucket_id' => $bucket_computer->compute(
					sorting_column: $sorting->column,
					granularity: $granularity,
					title: $row->title,
					title_base: $row->title_base,
					created_at: Carbon::parse($row->created_at),
					min_taken_at: $row->min_taken_at !== null ? Carbon::parse($row->min_taken_at) : null,
					max_taken_at: $row->max_taken_at !== null ? Carbon::parse($row->max_taken_at) : null,
				),
			];
		}

		DB::table('albums')->upsert($updates, ['id'], ['bucket_id']);

		// SettingsController::setConfigs dispatches AlbumListingCacheFlushRequested
		// (a synchronous, coarse flush) before dispatching this queued job — the
		// global-tag flush alone can be won by a request that repopulates
		// albumChildrenTag(null) with pre-upsert bucket_id data before this job
		// runs. Evict that tag again now that the new bucket_id values are
		// actually committed, closing the refill race.
		AlbumChildrenChanged::dispatch([null]);
	}
}
