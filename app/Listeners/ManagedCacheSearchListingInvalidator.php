<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;

/**
 * Evicts the v3 search cache (Feature 069, FR-069-15).
 *
 * Deliberately the bluntest invalidator in this codebase: every handler does
 * the same thing, because every cached search entry carries the single
 * {@see CacheKeyProvider::searchListingTag()} and nothing finer.
 *
 * That is a correctness requirement, not laziness. Its siblings
 * ({@see ManagedCachePhotoListingInvalidator}, {@see ManagedCacheMapListingInvalidator})
 * can evict per album because their cached responses are partitioned by album.
 * A search result is not: a photo edited in one album can enter or leave a
 * result set scoped to a completely different album, or to none at all, purely
 * because its title now matches. There is no album-shaped partition of the
 * search cache that would be safe to keep warm, so eviction is all-or-nothing.
 */
class ManagedCacheSearchListingInvalidator
{
	public function __construct(
		private ManagedCacheService $cache,
		private CacheKeyProvider $cache_key_provider,
	) {
	}

	/**
	 * Every mutation that can change what a search matches routes here.
	 * The event object itself is unused — see the class docblock for why no
	 * narrower eviction is available.
	 */
	public function handle(object $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->searchListingTag());
	}
}
