<?php

namespace App\Metadata\Cache;

final readonly class RouteCacheConfig
{
	/**
	 * Configuration of a route caching.
	 *
	 * @param string|null $tag            tags to quickly find the keys that need to be cleared
	 * @param bool        $user_dependant whether the route has data depending of the user
	 * @param string[]    $extra          extra parameters to be used in the cache key
	 *
	 * @return void
	 */
	public function __construct(
		public ?string $tag,
		public bool $user_dependant = false,
		public array $extra = [],
	) {
	}
}