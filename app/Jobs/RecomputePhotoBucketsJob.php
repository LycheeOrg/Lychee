<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\JobQueue;
use App\Events\PhotoBucketsRecomputed;
use App\Jobs\Traits\HasSharedUniqueLockTrait;
use App\Services\BucketKeyWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Rewrites `bucket_key` of every `photo_album` row of one photo — the
 * trigger for a change of the photo's own bucket-relevant columns
 * (`taken_at`, `created_at`, `title`, `is_highlighted`, `type`,
 * `rating_avg`; FR-092-10). Each linked album keeps its own effective sort
 * column, resolved per row by {@see BucketKeyWriter}.
 *
 * Unique until processing, see {@see RecomputeAlbumStatsJob}.
 */
#[Queue(JobQueue::LOW)]
class RecomputePhotoBucketsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;
	use HasSharedUniqueLockTrait;

	public int $tries = 3;

	/**
	 * Lifetime of the unique lock, in seconds.
	 */
	public int $uniqueFor = 3600;

	public function __construct(
		public string $photo_id,
	) {
	}

	public function uniqueId(): string
	{
		return $this->photo_id;
	}

	public function handle(): void
	{
		$writer = resolve(BucketKeyWriter::class);
		$album_ids = [];
		$writer->writePhotoAlbumKeys(
			fn ($query) => $query->where('photo_id', '=', $this->photo_id),
			on_chunk: function (Collection $keys) use (&$album_ids): void {
				$album_ids = [...$album_ids, ...$keys->pluck('album_id')->all()];
			},
		);

		if ($album_ids === []) {
			return;
		}

		// Dispatched after the write landed: the bulk write bypasses
		// Eloquent events, and this is the only signal that the affected
		// albums' photo-listing cache is now stale.
		PhotoBucketsRecomputed::dispatch($album_ids);
	}
}
