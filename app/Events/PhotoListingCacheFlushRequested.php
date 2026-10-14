<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Requests one coarse, instance-wide flush of every cached photo listing
 * (every `buckets`/`ratios`/`details` entry carries
 * {@see \App\Services\Cache\CacheKeyProvider::photoListingGlobalTag()}),
 * mirroring {@see AlbumListingCacheFlushRequested}.
 *
 * Dispatched when an instance-wide photo bucket setting changes
 * (FR-092-13): directly for `timeline_photos_granularity` and
 * `photo_title_bucket_prefix_length`, and by
 * {@see \App\Jobs\RecomputeAllPhotoBucketsJob} once the keys are rewritten.
 */
class PhotoListingCacheFlushRequested
{
	use Dispatchable;
}
