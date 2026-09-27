<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Search\StructOfArrays\QuerySearchAlbumRights;
use App\Actions\Search\StructOfArrays\QuerySearchAlbums;
use App\Actions\Search\StructOfArrays\QuerySearchPhotoDetails;
use App\Actions\Search\StructOfArrays\QuerySearchPhotos;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\Search\GetSearchV3DetailsRequest;
use App\Http\Requests\Search\GetSearchV3Request;
use App\Http\Resources\V3\AlbumDataResource;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Http\Resources\V3\PhotoDetailResource;
use App\Http\Resources\V3\SearchPhotoResource;
use App\Models\User;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves the v3 search family (Feature 069): `GET /api/v3/Search/Photos`,
 * `/Search/Photos/details`, `/Search/albums` and `/Search/albums/rights`.
 *
 * Direct structural precedent:
 * {@see \App\Http\Controllers\Gallery\MapListingController} — like Map, search
 * is a scope that is not an album, so it gets its own route family rather than
 * being reached through `/Albums/{album_id}/...` (Q-069-01).
 */
class SearchListingController extends Controller
{
	public function __construct(
		protected QuerySearchPhotos $query_search_photos,
		protected QuerySearchPhotoDetails $query_search_photo_details,
		protected QuerySearchAlbums $query_search_albums,
		protected QuerySearchAlbumRights $query_search_album_rights,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	// ── GET /Search/Photos ──────────────────────────────────────────

	public function photos(GetSearchV3Request $request): SearchPhotoResource
	{
		$user = $this->user();
		$key = $this->cache_key_provider->searchPhotosKey(
			$this->scopeDigest($request, $request->photoSortingCriterion() !== null),
			$user?->id,
			$this->cache_key_provider->unlockedAlbumsDigest(),
		);

		return $this->remember($request, $key, fn (): SearchPhotoResource => $this->query_search_photos->do(
			$request->tokens(),
			$request->origin(),
			$user,
			$request->photoSortingCriterion(),
		));
	}

	// ── GET /Search/Photos/details ──────────────────────────────────

	public function details(GetSearchV3DetailsRequest $request): PhotoDetailResource
	{
		$user = $this->user();
		$key = $this->cache_key_provider->searchPhotoDetailsKey(
			$this->scopeDigest($request, false),
			$this->cache_key_provider->searchPhotoIdsDigest($request->photoIds()),
			$user?->id,
			$this->cache_key_provider->unlockedAlbumsDigest(),
		);

		return $this->remember($request, $key, fn (): PhotoDetailResource => $this->query_search_photo_details->do(
			$request->tokens(),
			$request->origin(),
			$user,
			$request->photoIds(),
		));
	}

	// ── GET /Search/albums ──────────────────────────────────────────

	public function albums(GetSearchV3Request $request): AlbumDataResource
	{
		$user = $this->user();
		$key = $this->cache_key_provider->searchAlbumsKey(
			$this->scopeDigest($request, $request->albumSortingCriterion() !== null),
			$user?->id,
			$this->cache_key_provider->unlockedAlbumsDigest(),
		);

		return $this->remember($request, $key, fn (): AlbumDataResource => $this->query_search_albums->do(
			$request->tokens(),
			$request->origin(),
			$user,
			$request->albumSortingCriterion(),
		));
	}

	// ── GET /Search/albums/rights ───────────────────────────────────

	public function albumRights(GetSearchV3Request $request): AlbumRightsResource
	{
		$user = $this->user();
		$key = $this->cache_key_provider->searchAlbumRightsKey(
			$this->scopeDigest($request, false),
			$user?->id,
			$this->cache_key_provider->unlockedAlbumsDigest(),
		);

		return $this->remember($request, $key, fn (): AlbumRightsResource => $this->query_search_album_rights->do(
			$request->tokens(),
			$request->origin(),
			$user,
		));
	}

	/**
	 * Every search cache entry carries exactly one tag (FR-069-15). Search is a
	 * cross-album scope, so there is no finer partition that would be correct to
	 * maintain — any photo or album mutation anywhere can change any result.
	 *
	 * @template T
	 *
	 * @param \Closure(): T $callback
	 *
	 * @return T
	 */
	private function remember(GetSearchV3Request $request, string $key, \Closure $callback): mixed
	{
		return $this->managed_cache_service->rememberIf(
			$request->configs()->getValueAsBool('managed_cache_albums_enabled'),
			$key,
			[
				$this->cache_key_provider->searchListingTag(),
				$this->cache_key_provider->userTag($this->user()?->id),
			],
			$callback,
			ttl: $request->configs()->getValueAsInt('managed_cache_ttl'),
		);
	}

	/**
	 * The sort is only part of the cache identity when the caller actually
	 * supplied one — otherwise both tiers fall back to their own default, and
	 * folding a `null` sort into the digest would just fragment the cache.
	 */
	private function scopeDigest(GetSearchV3Request $request, bool $include_sorting): string
	{
		$sorting_column = $include_sorting ? $request->validated(RequestAttribute::SORTING_COLUMN_ATTRIBUTE) : null;
		$sorting_order = $include_sorting ? $request->validated(RequestAttribute::SORTING_ORDER_ATTRIBUTE) : null;

		return $this->cache_key_provider->searchScopeDigest(
			$request->tokens(),
			$request->origin()?->id,
			is_string($sorting_column) ? $sorting_column : null,
			is_string($sorting_order) ? $sorting_order : null,
		);
	}

	private function user(): ?User
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user;
	}
}
