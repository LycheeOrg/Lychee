<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Assets\DbBool;
use App\Events\AlbumPhotoSortingChanged;
use App\Models\Album;
use App\Services\PhotoBucketComputer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recomputes `bucket_id` for every `photo_album` row of one album's direct
 * photos — the trigger for when the album's own
 * `sorting_col`/`sorting_order`/`photo_timeline` (its *photo*-sort settings)
 * change, every direct photo's `bucket_id` (governed by this album, not the
 * photo itself) needs recomputing.
 *
 * Direct structural precedent: {@see RecomputeChildAlbumBucketsJob}.
 * Performs exactly one `SELECT` (raw rows, no Eloquent hydration of the
 * photos themselves) and one bulk `upsert()` covering every direct photo in
 * one round trip, never one save per photo.
 */
class RecomputeAlbumPhotoBucketsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 3;

	public function __construct(
		public string $album_id,
	) {
	}

	public function handle(): void
	{
		$album = Album::where('id', '=', $this->album_id)->first();
		if ($album === null) {
			Log::channel('jobs')->warning("Album {$this->album_id} not found, skipping photo bucket recompute.");

			return;
		}

		$sorting_column = $album->getEffectivePhotoSorting()->column;
		$bucket_computer = resolve(PhotoBucketComputer::class);
		$granularity = $bucket_computer->resolveGranularity($album->photo_timeline);

		$rows = DB::table('photo_album')
			->join('photos', 'photos.id', '=', 'photo_album.photo_id')
			->where('photo_album.album_id', '=', $album->id)
			->select([
				'photo_album.photo_id',
				'photo_album.album_id',
				'photos.title',
				'photos.title_base',
				'photos.created_at',
				'photos.taken_at',
				'photos.is_highlighted',
				'photos.type',
				'photos.rating_avg',
			])
			->get();

		if ($rows->isEmpty()) {
			return;
		}

		$updates = [];
		foreach ($rows as $row) {
			$updates[] = [
				'photo_id' => $row->photo_id,
				'album_id' => $row->album_id,
				'bucket_id' => $bucket_computer->compute(
					sorting_column: $sorting_column,
					granularity: $granularity,
					title: $row->title,
					title_base: $row->title_base ?? '',
					created_at: Carbon::parse($row->created_at),
					taken_at: $row->taken_at !== null ? Carbon::parse($row->taken_at) : null,
					is_highlighted: DbBool::parse($row->is_highlighted),
					type: $row->type ?? '',
					rating_avg: $row->rating_avg,
				),
			];
		}

		DB::table('photo_album')->upsert($updates, ['photo_id', 'album_id'], ['bucket_id']);

		// Dispatched here, after the write has actually landed, rather than
		// at the call site that queues this job - the write above bypasses
		// Eloquent events, and firing the eviction any earlier would let it
		// race ahead of this (queued, possibly-delayed) job and leave the
		// cache serving stale `bucket_id` values in between.
		AlbumPhotoSortingChanged::dispatch([$this->album_id]);
	}
}
