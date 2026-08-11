<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\UserGroupMembershipChanged;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;

/**
 * Evicts every album-listing cache entry keyed to a user whose group
 * membership (or role within a group) just changed.
 */
class ManagedCacheUserListingInvalidator
{
	public function __construct(
		private ManagedCacheService $cache,
		private CacheKeyProvider $cache_key_provider,
	) {
	}

	public function handle(UserGroupMembershipChanged $event): void
	{
		$this->cache->forgetTag($this->cache_key_provider->userTag($event->user_id));
	}
}
