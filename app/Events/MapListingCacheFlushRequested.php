<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Requests one coarse, instance-wide flush of every Map cache entry (every
 * cached `buckets`/`Photos`/`tracks` response, across every scope, carries
 * {@see \App\Services\Cache\CacheKeyProvider::mapListingGlobalTag()} in
 * addition to its own scope-specific tag) - Feature 067's FR-067-24,
 * mirroring {@see AlbumListingCacheFlushRequested}'s own precedent
 * (Q-053-05) for the sibling album-listing cache.
 *
 * Dispatched when any of `hide_nsfw_in_map`/`map_include_subalbums`/
 * `map_display`/`map_display_public` changes — none of these has a
 * per-photo/per-album domain event to hook, unlike a routine photo edit.
 */
class MapListingCacheFlushRequested
{
	use Dispatchable;
}
