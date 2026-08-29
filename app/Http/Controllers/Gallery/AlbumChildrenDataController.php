<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Requests\Album\GetAlbumChildrenDataRequest;
use App\Http\Resources\V3\AlbumChildrenDataResource;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Serves `GET /api/v3/Albums/{album_id}/children` (Feature 061,
 * API-061-02): the per-direct-child render data an album tile needs, as one
 * whole-album-at-once Struct-of-Arrays response, built from a single flat
 * `toBase()` query with zero joins beyond {@see AlbumQueryPolicy::applyVisibilityFilter()}'s
 * own (NFR-061-07) — identical scoping/policy to
 * {@see \App\Repositories\AlbumRepository::getChildrenPaginated()} and
 * {@see AlbumBucketController}'s own query (NFR-061-08).
 */
class AlbumChildrenDataController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	public function index(GetAlbumChildrenDataRequest $request): AlbumChildrenDataResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->albumChildrenDataKey($album->id, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[$this->cache_key_provider->albumChildrenTag($album->id)],
			fn (): AlbumChildrenDataResource => $this->queryChildren($album, $user),
			ttl: $ttl,
		);
	}

	private function queryChildren(Album $album, ?User $user): AlbumChildrenDataResource
	{
		$query = Album::query()->where('parent_id', '=', $album->id);
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		$rows = $query
			->select([
				'albums.id',
				'base_albums.title',
				'albums.cover_id',
				'albums.auto_cover_id_max_privilege',
				'albums.auto_cover_id_least_privilege',
				'base_albums.owner_id',
				'albums.bucket_id',
				'computed_access_permissions.password',
				'base_albums.is_nsfw',
				'albums.num_children',
				'albums.num_photos',
				'base_albums.created_at',
				'albums.min_taken_at',
				'albums.max_taken_at',
			])
			->selectRaw('SUBSTR(base_albums.description, 1, 100) as description')
			->toBase()
			->get();

		return $this->toResource($rows, $user);
	}

	/**
	 * @param Collection<int,object{id:string,title:string,description:?string,cover_id:?string,auto_cover_id_max_privilege:?string,auto_cover_id_least_privilege:?string,owner_id:int,bucket_id:?string,password:?string,is_nsfw:mixed,num_children:int|string,num_photos:int|string,created_at:string,min_taken_at:?string,max_taken_at:?string}> $rows
	 */
	private function toResource(Collection $rows, ?User $user): AlbumChildrenDataResource
	{
		$ids = [];
		$titles = [];
		$descriptions = [];
		$cover_ids = [];
		$bucket_ids = [];
		$is_password_requireds = [];
		$is_nsfws = [];
		$has_subalbums = [];
		$num_photos = [];
		$num_subalbums = [];
		$created_ats = [];
		$min_taken_ats = [];
		$max_taken_ats = [];

		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			$descriptions[] = $row->description ?? '';
			$cover_ids[] = AlbumListController::resolveCoverId($row, $user);
			$bucket_ids[] = $row->bucket_id ?? 'unknown';
			$is_password_requireds[] = $row->password !== null;
			$is_nsfws[] = filter_var($row->is_nsfw, FILTER_VALIDATE_BOOLEAN);
			$has_subalbums[] = ((int) $row->num_children) > 0;
			$num_photos[] = (int) $row->num_photos;
			$num_subalbums[] = (int) $row->num_children;
			$created_ats[] = $row->created_at;
			$min_taken_ats[] = $row->min_taken_at;
			$max_taken_ats[] = $row->max_taken_at;
		}

		return new AlbumChildrenDataResource(
			ids: $ids,
			titles: $titles,
			descriptions: $descriptions,
			cover_ids: $cover_ids,
			bucket_ids: $bucket_ids,
			is_password_requireds: $is_password_requireds,
			is_nsfws: $is_nsfws,
			has_subalbums: $has_subalbums,
			num_photos: $num_photos,
			num_subalbums: $num_subalbums,
			created_ats: $created_ats,
			min_taken_ats: $min_taken_ats,
			max_taken_ats: $max_taken_ats,
		);
	}
}
