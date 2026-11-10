<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\JobQueue;
use App\Events\AlbumListingCacheFlushRequested;
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
 * Rewrites `bucket_key` of every album — the trigger for a change of
 * `sorting_albums_col` or `title_bucket_mode` (FR-092-13). Root albums and
 * the children of every parent without its own album sorting follow the
 * instance default, so no narrower scope is exact.
 *
 * Chunked by {@see BucketKeyWriter} (FR-092-14).
 *
 * Unique until processing, see {@see RecomputeAlbumStatsJob}: one key for
 * the whole instance.
 */
#[Queue(JobQueue::LOW)]
class RecomputeAllAlbumBucketsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
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
		$writer->writeAlbumKeys(fn ($query) => $query);

		// The settings controller flushed before dispatching this job; flush
		// again now that the keys are committed, closing the refill race.
		AlbumListingCacheFlushRequested::dispatch();
	}
}
