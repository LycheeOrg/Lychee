<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Cache;

use App\Enum\CacheTag;

final readonly class RouteCacheConfig
{
	/**
	 * Configuration of a route caching.
	 *
	 * @param CacheTag|null $tag            tags to quickly find the keys that need to be cleared
	 * @param bool          $user_dependant whether the route has data depending of the user
	 * @param string[]      $extra          extra parameters to be used in the cache key
	 *
	 * @return void
	 */
	public function __construct(
		public ?CacheTag $tag,
		public bool $user_dependant = false,
		public array $extra = [],
	) {
	}
}