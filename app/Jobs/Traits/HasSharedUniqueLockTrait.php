<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs\Traits;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Store of the unique lock of a `ShouldBeUnique` job.
 *
 * The lock is taken where the job is dispatched (web container) and released
 * where it is processed (worker container). On an asynchronous queue both
 * must see the same lock: it lives in the database, which they always share,
 * whatever the cache driver (ADR-090-01). On `sync` both happen in the same
 * process and the default store is enough.
 */
trait HasSharedUniqueLockTrait
{
	public function uniqueVia(): Repository
	{
		return config('queue.default') === 'sync' ? Cache::store() : Cache::store('database');
	}
}
