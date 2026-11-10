<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\JobQueue;
use App\Events\AlbumPhotoSortingChanged;
use App\Jobs\Traits\HasSharedUniqueLockTrait;
use App\Services\BucketKeyWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Rewrites `bucket_key` of every `photo_album` row of one album — the
 * trigger for a change of the album's own `sorting_col` (FR-092-11). A
 * change of only `sorting_order` or `photo_timeline` rewrites no key.
 *
 * Chunked by {@see BucketKeyWriter} (FR-092-14).
 *
 * Unique until processing, see {@see RecomputeAlbumStatsJob}.
 */
#[Queue(JobQueue::LOW)]
class RecomputeAlbumPhotoBucketsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
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
		public string $album_id,
	) {
	}

	public function uniqueId(): string
	{
		return $this->album_id;
	}

	public function handle(): void
	{
		$writer = resolve(BucketKeyWriter::class);
		$processed = $writer->writePhotoAlbumKeys(fn ($query) => $query->where('album_id', '=', $this->album_id));

		// Dispatched after the write landed: firing the eviction any earlier
		// would let a request refill the cache with the previous keys.
		AlbumPhotoSortingChanged::dispatchIf($processed > 0, [$this->album_id]);
	}
}
