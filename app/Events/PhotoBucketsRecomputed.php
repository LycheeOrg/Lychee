<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by {@see \App\Jobs\RecomputePhotoBucketsJob} once its bulk
 * `upsert()` has actually landed - the job bypasses Eloquent events
 * entirely, so this is the only signal that the photo-listing cache for
 * the affected albums is now stale. Mirrors {@see AlbumPhotoSortingChanged}
 * for {@see \App\Jobs\RecomputeAlbumPhotoBucketsJob}, except the trigger
 * here varies per call site (rating/title/taken_at/is_highlighted changes)
 * rather than being one fixed cause, so this event names the effect
 * instead of the cause.
 */
class PhotoBucketsRecomputed
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * @param array<int,string> $album_ids IDs of the albums whose photo-listing cache is now stale, batched so listeners can act once per call
	 */
	public function __construct(public array $album_ids)
	{
	}
}
