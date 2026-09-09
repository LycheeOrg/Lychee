<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Assets\DbBool;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Events\PhotoBucketsRecomputed;
use App\Models\Extensions\UTCBasedTimes;
use App\Services\PhotoBucketComputer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Recomputes `bucket_id` for every `photo_album` row of one photo — the
 * trigger for when a photo's own bucket-relevant columns
 * (`created_at`/`taken_at`/`title`/`title_base`/`is_highlighted`/`type`/
 * `rating_avg`) change, every album this photo is linked into needs its
 * own `bucket_id` recomputed, since a single photo can be linked into
 * several albums whose effective `sorting_col`/`photo_timeline` settings
 * genuinely differ —
 * unlike {@see RecomputeAlbumPhotoBucketsJob}, this job cannot resolve one
 * shared sort setting up front; it resolves each linked album's own
 * settings per row, via a raw `base_albums` join rather than N lazy
 * Eloquent loads.
 *
 * Performs exactly one `SELECT` (raw rows, no Eloquent hydration) and one
 * bulk `upsert()`, never one save per linked album — in practice a photo is
 * linked into a small, bounded number of albums, never album-count-scale.
 */
class RecomputePhotoBucketsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;
	use UTCBasedTimes;

	public int $tries = 3;

	public function __construct(
		public string $photo_id,
	) {
	}

	public function handle(): void
	{
		$bucket_computer = resolve(PhotoBucketComputer::class);
		$global_default_column = PhotoSortingCriterion::createDefault()->column;

		$rows = DB::table('photo_album')
			->join('photos', 'photos.id', '=', 'photo_album.photo_id')
			->join('base_albums', 'base_albums.id', '=', 'photo_album.album_id')
			->where('photo_album.photo_id', '=', $this->photo_id)
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
				'base_albums.sorting_col',
				'base_albums.photo_timeline',
			])
			->get();

		if ($rows->isEmpty()) {
			return;
		}

		$updates = [];
		foreach ($rows as $row) {
			$sorting_column = $row->sorting_col !== null
				? ColumnSortingType::from($row->sorting_col)
				: $global_default_column;

			$candidate_granularity = $row->photo_timeline !== null
				? TimelinePhotoGranularity::from($row->photo_timeline)
				: null;
			$granularity = $bucket_computer->resolveGranularity($candidate_granularity);

			$updates[] = [
				'photo_id' => $row->photo_id,
				'album_id' => $row->album_id,
				'bucket_id' => $bucket_computer->compute(
					sorting_column: $sorting_column,
					granularity: $granularity,
					title: $row->title,
					title_base: $row->title_base ?? '',
					created_at: $this->asDateTime($row->created_at),
					taken_at: $row->taken_at !== null ? $this->asDateTime($row->taken_at) : null,
					is_highlighted: DbBool::parse($row->is_highlighted),
					type: $row->type ?? '',
					rating_avg: $row->rating_avg,
				),
			];
		}

		DB::table('photo_album')->upsert($updates, ['photo_id', 'album_id'], ['bucket_id']);

		// Dispatched here, after the write has actually landed, rather than
		// by each call site that queues this job - the write above bypasses
		// Eloquent events, and this is the only signal that the affected
		// albums' photo-listing cache is now stale.
		PhotoBucketsRecomputed::dispatch(array_column($updates, 'album_id'));
	}
}
