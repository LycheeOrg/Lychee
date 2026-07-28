<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\UserGroupMembershipChanged;
use App\Services\Cache\ManagedCacheService;

/**
 * Evicts the ManagedCacheService tag for a user whenever their group
 * membership changes (FR-052-07).
 */
class ManagedCacheUserInvalidator
{
	public function __construct(
		private ManagedCacheService $managed_cache_service,
	) {
	}

	public function handle(UserGroupMembershipChanged $event): void
	{
		$this->managed_cache_service->forgetTag('user:' . $event->user_id);
	}
}
