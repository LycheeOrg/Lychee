<?php

namespace App\Listeners;

use App\Events\TaggedRouteCacheUpdated;
use App\Metadata\Cache\RouteCacheManager;
use Illuminate\Support\Facades\Cache;

class TaggedRouteCacheCleaner
{
	/**
	 * Create the event listener.
	 */
	public function __construct(
		private RouteCacheManager $route_cache_manager,
	) {
	}

	/**
	 * Handle the event.
	 */
	public function handle(TaggedRouteCacheUpdated $event): void
	{
		$cached_routes = $this->route_cache_manager->retrieve_keys_for_tag($event->tag);

		foreach ($cached_routes as $route) {
			$cache_key = $this->route_cache_manager->gen_key($route);
			Cache::forget($cache_key);
		}

		if (Cache::supportsTags()) {
			Cache::tags($event->tag->value)->flush();
		}
	}
}
