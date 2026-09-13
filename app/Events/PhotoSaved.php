<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoSaved
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 *
	 * @param array<int,string>                                          $photo_ids      batched so listeners can resolve affected albums once per call instead of once per photo
	 * @param array<string,array{created_at?:?string,taken_at?:?string}> $previous_dates keyed by photo id, the RAW (un-cast) `created_at`/`taken_at` column values as they were immediately before this save - captured by the caller via `getRawOriginal()` before mutating/saving, since by the time this event is handled the DB row already holds the new values. Only meaningful for a photo whose bucket-relevant sort date may have changed; omit a photo's entry entirely when the caller doesn't know or didn't touch these columns. Lets {@see \App\Listeners\ManagedCachePhotoListingInvalidator::evictTimelineBucketsFor()} evict the photo's PREVIOUS Timeline bucket tag in addition to its current one.
	 */
	public function __construct(
		public array $photo_ids,
		public array $previous_dates = [],
	) {
	}
}
