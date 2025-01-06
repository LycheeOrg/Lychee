<?php

namespace App\Listeners;

use App\Enum\CacheTag;
use App\Enum\SmartAlbumType;
use App\Events\AlbumRouteCacheUpdated;
use App\Metadata\Cache\RouteCacheManager;
use App\Metadata\Cache\RouteCacher;

/**
 * We react to AlbumRouteCacheUpdated events and clear the associated cache.
 */
class AlbumCacheCleaner
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
	public function handle(AlbumRouteCacheUpdated $event): void
	{
		if ($event->album_id === null) {
			// this is a clear all.
			$routes = $this->route_cache_manager->retrieve_routes_for_tag(CacheTag::GALLERY);
			foreach ($routes as $route) {
				$this->route_cacher->forgetRoute($route);
			}

			return;
		}

		$routes = $this->route_cache_manager->retrieve_routes_for_tag(CacheTag::GALLERY, with_extra: false, without_extra: true);
		foreach ($routes as $route) {
			$this->route_cacher->forgetRoute($route);
		}

		if ($event->album_id === '') {
			$this->route_cacher->forgetRoute('api/Albums::get');

			// this is a clear all.
			return;
		}

		// Clear smart albums. Simple.
		collect(SmartAlbumType::cases())->each(function (SmartAlbumType $type) {
			$this->route_cacher->forgetTag($type->value);
		});

		$this->route_cacher->forgetTag($event->album_id);
	}
}
