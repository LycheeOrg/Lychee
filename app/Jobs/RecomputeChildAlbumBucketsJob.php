<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Models\Album;
use App\Services\AlbumBucketComputer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recomputes `bucket_id` for every **direct child** of one parent album
 * — the propagation direction {@see RecomputeAlbumStatsJob} does not already
 * cover: when the parent's own `album_sorting_col`/`album_sorting_order`/`album_timeline`
 * changes, every direct child's `bucket_id` (governed by the parent, not the child
 * itself) needs recomputing.
 *
 * Performs exactly one `SELECT` (raw rows, no Eloquent hydration) and one
 * bulk `upsert()` — a single `UPDATE`-shaped statement covering every
 * direct child in one round trip, never one save per child (see plan.md
 * Risks: this is a correctness requirement for this feature's premise at
 * 7,000+-child scale, not a style nit).
 */
class RecomputeChildAlbumBucketsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public $tries = 3;

	public function __construct(
		public string $parent_album_id,
	) {
	}

	public function handle(): void
	{
		$parent = Album::where('id', '=', $this->parent_album_id)->first();
		if ($parent === null) {
			Log::channel('jobs')->warning("Album {$this->parent_album_id} not found, skipping child bucket recompute.");

			return;
		}

		$sorting_column = $parent->getEffectiveAlbumSorting()->column;
		$bucket_computer = resolve(AlbumBucketComputer::class);
		$granularity = $bucket_computer->resolveGranularity($parent->album_timeline);

		$rows = DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('albums.parent_id', '=', $parent->id)
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
					sorting_column: $sorting_column,
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
	}
}
