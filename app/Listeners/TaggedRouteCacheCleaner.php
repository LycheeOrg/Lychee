<?php

namespace App\Listeners;

use App\Events\TaggedRouteCacheUpdated;
use App\Metadata\Cache\RouteCacheManager;
use App\Metadata\Cache\RouteCacher;

class TaggedRouteCacheCleaner
{
	/**
	 * Create the event listener.
	 */
	public function __construct(
		private RouteCacheManager $route_cache_manager,
		private RouteCacher $route_cacher,
	) {
	}

	/**
	 * Handle the event.
	 */
	public function handle(TaggedRouteCacheUpdated $event): void
	{
		$cached_routes = $this->route_cache_manager->retrieve_routes_for_tag($event->tag);
		foreach ($cached_routes as $route) {
			$this->route_cacher->forgetRoute($route);
		}
	}
}
