<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\JobQueue;
use App\Events\PhotoListingCacheFlushRequested;
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
 * Rewrites `bucket_key` of every `photo_album` row — the trigger for a
 * change of `sorting_photos_col` or `photo_title_bucket_mode`
 * (FR-092-13), then flushes every cached photo listing.
 *
 * Chunked by {@see BucketKeyWriter} (FR-092-14).
 *
 * Unique until processing, see {@see RecomputeAlbumStatsJob}: one key for
 * the whole instance.
 */
#[Queue(JobQueue::LOW)]
class RecomputeAllPhotoBucketsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
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

	public function handle(): void
	{
		$writer = resolve(BucketKeyWriter::class);
		$writer->writePhotoAlbumKeys(fn ($query) => $query);

		PhotoListingCacheFlushRequested::dispatch();
	}
}
