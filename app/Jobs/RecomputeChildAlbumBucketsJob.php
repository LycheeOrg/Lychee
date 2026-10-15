<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\JobQueue;
use App\Events\AlbumChildrenChanged;
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
 * Rewrites `bucket_key` of every **direct child** of one parent album —
 * the trigger for a change of the parent's `album_sorting_col`
 * (FR-092-12), the direction {@see RecomputeAlbumStatsJob} does not cover.
 * A change of only `album_sorting_order` or `album_timeline` rewrites no
 * key.
 *
 * Chunked by {@see BucketKeyWriter} (FR-092-14).
 *
 * Unique until processing, see {@see RecomputeAlbumStatsJob}.
 */
#[Queue(JobQueue::LOW)]
class RecomputeChildAlbumBucketsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;
	use HasSharedUniqueLockTrait;

	public $tries = 3;

	/**
	 * Lifetime of the unique lock, in seconds.
	 *
	 * @var int
	 */
	public $uniqueFor = 3600;

	public function __construct(
		public string $parent_album_id,
	) {
	}

	public function uniqueId(): string
	{
		return $this->parent_album_id;
	}

	public function handle(): void
	{
		$writer = resolve(BucketKeyWriter::class);
		$processed = $writer->writeAlbumKeys(fn ($query) => $query->where('a.parent_id', '=', $this->parent_album_id));

		// Evict the children listing again now that the keys are committed:
		// a request between the controller's flush and this job could have
		// refilled it with the previous keys.
		AlbumChildrenChanged::dispatchIf($processed > 0, [$this->parent_album_id]);
	}
}
