<?php

namespace App\Listeners;

use App\Enum\CacheTag;
use App\Enum\SmartAlbumType;
use App\Events\AlbumRouteCacheUpdated;
use App\Metadata\Cache\RouteCacheManager;
use App\Models\BaseAlbumImpl;
use Illuminate\Support\Facades\Cache;

class AlbumCacheCleaner
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
	public function handle(AlbumRouteCacheUpdated $event): void
	{
		// The quick way.
		if (Cache::supportsTags()) {
			Cache::tags(CacheTag::GALLERY->value)->flush();

			return;
		}

		$this->dropCachedRoutesWithoutExtra();

		// By default we refresh all the smart albums.
		$this->handleSmartAlbums();

		if ($event->album_id === null) {
			$this->handleAllAlbums();

			return;
		}

		// Root album => already taken care of with the route without extra.
		if ($event->album_id === '') {
			return;
		}

		$this->handleAlbumId($event->album_id);
	}

	/**
	 * Drop cache for all routes without extra (meaning which do not depend on album_id).
	 *
	 * @return void
	 */
	private function dropCachedRoutesWithoutExtra(): void
	{
		$cached_routes_without_extra = $this->route_cache_manager->retrieve_keys_for_tag(CacheTag::GALLERY, without_extra: true);
		foreach ($cached_routes_without_extra as $route) {
			$cache_key = $this->route_cache_manager->gen_key(uri: $route);
			Cache::forget($cache_key);
		}
	}

	/**
	 * Drop cache for all routes related to albums.
	 *
	 * @return void
	 */
	private function handleAllAlbums(): void
	{
		// The long way.
		$cached_routes_with_extra = $this->route_cache_manager->retrieve_keys_for_tag(CacheTag::GALLERY, with_extra: true);
		BaseAlbumImpl::select('id')->get()->each(function (BaseAlbumImpl $album) use ($cached_routes_with_extra) {
			$extra = ['album_id' => $album->id];
			foreach ($cached_routes_with_extra as $route) {
				$cache_key = $this->route_cache_manager->gen_key(uri: $route, extras: $extra);
				Cache::forget($cache_key);
			}
		});
	}

	/**
	 * Drop cache fro all routes related to an album.
	 *
	 * @param string $album_id
	 *
	 * @return void
	 */
	private function handleAlbumId(string $album_id): void
	{
		$cached_routes_with_extra = $this->route_cache_manager->retrieve_keys_for_tag(CacheTag::GALLERY, with_extra: true);
		$extra = ['album_id' => $album_id];

		foreach ($cached_routes_with_extra as $route) {
			$cache_key = $this->route_cache_manager->gen_key(uri: $route, extras: $extra);
			Cache::forget($cache_key);
		}
	}

	/**
	 * Drop cache for all smart albums too.
	 *
	 * @return void
	 */
	private function handleSmartAlbums(): void
	{
		$cached_routes_with_extra = $this->route_cache_manager->retrieve_keys_for_tag(CacheTag::GALLERY, with_extra: true);
		// Also reset smart albums ;)
		collect(SmartAlbumType::cases())->each(function (SmartAlbumType $type) use ($cached_routes_with_extra) {
			$extra = ['album_id' => $type->value];
			foreach ($cached_routes_with_extra as $route) {
				$cache_key = $this->route_cache_manager->gen_key(uri: $route, extras: $extra);
				Cache::forget($cache_key);
			}
		});
	}
}
