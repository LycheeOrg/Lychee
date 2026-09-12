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

		$key = $this->cache_key_provider->photoBucketsKey($album->get_id(), $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->photoListingTag($album->get_id()),
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
		$bucket_ids = $request->bucketIds();
		$photo_ids = $request->photoIds();

		$scope_digest = $this->cache_key_provider->photoRatiosScopeDigest($bucket_ids, $photo_ids);
		$key = $this->cache_key_provider->photoRatiosKey($album->get_id(), $scope_digest, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			$this->photoRatiosTags($album->get_id(), $bucket_ids, $user?->id),
			fn (): PhotoRatioResource => $this->query_photo_ratios->do($album, $user, $bucket_ids, $photo_ids),
			ttl: $ttl,
		);
	}

	/**
	 * Cache tags for one `ratios` response: the coarse per-album tag,
	 * always; plus one fine per-bucket tag (FR-066-09) for each requested
	 * bucket, so a bucket-scoped request's cache entry can be evicted
	 * without thrashing every other cached bucket for the same album.
	 * A whole-scope (`$bucket_ids === null`) or `photo_ids[]`-scoped
	 * request carries only the coarse tag - there is no fixed bucket set to
	 * name a fine tag after.
	 *
	 * @param string[]|null $bucket_ids
	 *
	 * @return string[]
	 */
	private function photoRatiosTags(string $album_id, ?array $bucket_ids, int|string|null $user_id): array
	{
		$tags = [
			$this->cache_key_provider->photoListingTag($album_id),
			$this->cache_key_provider->userTag($user_id),
		];

		if ($bucket_ids !== null) {
			$tags = array_merge($tags, $this->cache_key_provider->photoListingBucketTags($album_id, $bucket_ids));
		}

		return $tags;
	}

	// ── GET /Albums/{album_id}/Photos/details ───────────────────────

	public function details(GetPhotoDetailsRequest $request): PhotoDetailResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();
		$bucket_id = $request->bucketId();

		$scope_digest = $this->cache_key_provider->photoDetailsScopeDigest($bucket_id, $request->photoIds());
		$key = $this->cache_key_provider->photoDetailsKey($album->get_id(), $scope_digest, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		$tags = [
			$this->cache_key_provider->photoListingTag($album->get_id()),
			$this->cache_key_provider->userTag($user?->id),
		];
		if ($bucket_id !== null) {
			// Fine per-bucket tag (FR-066-09) - a `photo_ids[]`-scoped
			// request has no single fixed bucket to name a fine tag after,
			// so it carries only the coarse tag, same as before.
			$tags[] = $this->cache_key_provider->photoListingBucketTag($album->get_id(), $bucket_id);
		}

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			$tags,
			fn (): PhotoDetailResource => $this->query_photo_details->do($album, $user, $bucket_id, $request->photoIds()),
			ttl: $ttl,
		);
	}
}
