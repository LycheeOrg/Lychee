<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Http\Middleware\ClearBrowserCache;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Flags the current request so that {@see ClearBrowserCache} wipes the
 * browser cache: assets cached for one viewer must not be reused by the
 * next one (Feature 102, FR-102-08).
 */
class ClearBrowserCacheOnAuthChange
{
	public function handle(Login|Logout $event): void
	{
		request()->attributes->set(ClearBrowserCache::FLAG, true);
	}
}
