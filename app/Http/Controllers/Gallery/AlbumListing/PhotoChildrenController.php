<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Actions\Photo\StructOfArrays\QueryPhotoBuckets;
use App\Actions\Photo\StructOfArrays\QueryPhotoDetails;
use App\Actions\Photo\StructOfArrays\QueryPhotoRatios;
use App\Http\Requests\Photo\GetPhotoBucketsRequest;
use App\Http\Requests\Photo\GetPhotoDetailsRequest;
use App\Http\Requests\Photo\GetPhotoRatiosRequest;
use App\Http\Resources\V3\PhotoBucketResource;
use App\Http\Resources\V3\PhotoDetailResource;
use App\Http\Resources\V3\PhotoRatioResource;
use App\Models\User;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves the photo tier: `GET /api/v3/Albums/{album_id}/Photos`,
 * `/Albums/{album_id}/Photos/buckets`, `/Albums/{album_id}/Photos/details`.
 * Direct structural precedent:
 * {@see \App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController}.
 */
class PhotoChildrenController extends Controller
{
	public function __construct(
		protected QueryPhotoBuckets $query_photo_buckets,
		protected QueryPhotoRatios $query_photo_ratios,
		protected QueryPhotoDetails $query_photo_details,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	// ── GET /Albums/{album_id}/Photos/buckets ──────────────────────

	public function buckets(GetPhotoBucketsRequest $request): PhotoBucketResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->photoBucketsKey($album->id, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->photoListingTag($album->id),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): PhotoBucketResource => $this->query_photo_buckets->do($album, $user),
			ttl: $ttl,
		);
	}

	// ── GET /Albums/{album_id}/Photos ───────────────────────────────

	public function index(GetPhotoRatiosRequest $request): PhotoRatioResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->photoRatiosKey($album->id, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->photoListingTag($album->id),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): PhotoRatioResource => $this->query_photo_ratios->do($album, $user),
			ttl: $ttl,
		);
	}

	// ── GET /Albums/{album_id}/Photos/details ───────────────────────

	public function details(GetPhotoDetailsRequest $request): PhotoDetailResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		$scope_digest = $this->cache_key_provider->photoDetailsScopeDigest($request->bucketId(), $request->photoIds());
		$key = $this->cache_key_provider->photoDetailsKey($album->id, $scope_digest, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->photoListingTag($album->id),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): PhotoDetailResource => $this->query_photo_details->do($album, $user, $request->bucketId(), $request->photoIds()),
			ttl: $ttl,
		);
	}
}
