<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Map\QueryAlbumMapPoints;
use App\Actions\Map\QueryMapBuckets;
use App\Actions\Map\QueryMapPhotos;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\Map\GetMapAlbumRequest;
use App\Http\Requests\Map\GetMapBucketsRequest;
use App\Http\Requests\Map\GetMapPhotosRequest;
use App\Http\Requests\Map\GetMapTracksRequest;
use App\Http\Resources\Models\TrackResource;
use App\Http\Resources\V3\MapBucketResource;
use App\Http\Resources\V3\MapPhotoResource;
use App\Http\Resources\V3\MapPointResource;
use App\Models\Album;
use App\Models\Track;
use App\Models\User;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves the Map's bucket-tiered API (Feature 067): `GET /api/v3/Map/buckets`,
 * `/Map/Photos`, `/Map/tracks`, and the album map header's `/Map/album`
 * (Feature 086). Direct structural precedent:
 * {@see \App\Http\Controllers\Gallery\AlbumListing\PhotoChildrenController}.
 */
class MapListingController extends Controller
{
	public function __construct(
		protected QueryMapBuckets $query_map_buckets,
		protected QueryMapPhotos $query_map_photos,
		protected QueryAlbumMapPoints $query_album_map_points,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	// ── GET /Map/buckets ────────────────────────────────────────────

	public function buckets(GetMapBucketsRequest $request): MapBucketResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();
		// Server-derived, never a client-supplied parameter (Q-067-08).
		$include_sub_albums = $request->configs()->getValueAsBool('map_include_subalbums');
		$viewport = $request->viewport();
		$scope = $album?->get_id() ?? 'root';

		$unlocked_digest = $this->cache_key_provider->unlockedAlbumsDigest();
		$key = $this->cache_key_provider->mapBucketsKey($scope, $viewport->snapToGrid(), $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->mapListingTag($scope),
				$this->cache_key_provider->mapListingGlobalTag(),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): MapBucketResource => $this->query_map_buckets->do($album, $user, $viewport, $include_sub_albums),
			ttl: $ttl,
		);
	}

	// ── GET /Map/Photos ─────────────────────────────────────────────

	public function photos(GetMapPhotosRequest $request): MapPhotoResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();
		$include_sub_albums = $request->configs()->getValueAsBool('map_include_subalbums');
		$viewport = $request->viewport();
		$scope = $album?->get_id() ?? 'root';

		$unlocked_digest = $this->cache_key_provider->unlockedAlbumsDigest();
		$key = $this->cache_key_provider->mapPhotosKey($scope, $viewport->snapToGrid(), $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->mapListingTag($scope),
				$this->cache_key_provider->mapListingGlobalTag(),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): MapPhotoResource => $this->query_map_photos->do($album, $user, $viewport, $include_sub_albums),
			ttl: $ttl,
		);
	}

	// ── GET /Map/tracks ─────────────────────────────────────────────

	public function tracks(GetMapTracksRequest $request): array
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();
		$album_id = $album?->get_id() ?? 'root';

		$unlocked_digest = $this->cache_key_provider->unlockedAlbumsDigest();
		$key = $this->cache_key_provider->mapTracksKey($album_id, $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->mapListingTag($album_id),
				$this->cache_key_provider->mapListingGlobalTag(),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): array => $this->buildTrackResources($album),
			ttl: $ttl,
		);
	}

	// ── GET /Map/album ──────────────────────────────────────────────

	public function album(GetMapAlbumRequest $request): MapPointResource
	{
		/** @var AbstractAlbum $album */
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();
		$include_sub_albums = $request->configs()->getValueAsBool('map_include_subalbums');
		$album_id = $album->get_id();

		$unlocked_digest = $this->cache_key_provider->unlockedAlbumsDigest();
		$key = $this->cache_key_provider->mapAlbumPointsKey($album_id, $include_sub_albums, $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->mapListingTag($album_id),
				$this->cache_key_provider->mapListingGlobalTag(),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): MapPointResource => $this->query_album_map_points->do($album, $user, $include_sub_albums),
			ttl: $ttl,
		);
	}

	/**
	 * @return TrackResource[]
	 */
	private function buildTrackResources(?AbstractAlbum $album): array
	{
		$tracks = $album instanceof Album ? $album->tracks : collect();

		return $tracks->map(fn (Track $track) => new TrackResource($track))->all();
	}
}
