<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Actions\Album\StructOfArrays\Traits\BuildsAlbumCategoryResource;
use App\Assets\DbBool;
use App\DTO\AlbumSortingCriterion;
use App\Http\Requests\Album\GetAlbumCategoryRequest;
use App\Http\Resources\V3\AlbumCategoryResource;
use App\Http\Resources\V3\AlbumCategoryRightsResource;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Serves the flat, un-bucketed, un-scoped tag-album listings:
 * `GET /Albums/tags`, `/Albums/tags/rights` — always one flat, ungrouped
 * list, never per-owner buckets, never a `/buckets` route (NG9).
 */
class AlbumTagController extends Controller
{
	use BuildsAlbumCategoryResource;

	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	// ── GET /Albums/tags ─────────────────────────────────────────────

	public function tags(GetAlbumCategoryRequest $request): AlbumCategoryResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$sorting = AlbumSortingCriterion::createDefault();

		$key = $this->cache_key_provider->tagAlbumsListingKey($user?->id, $sorting);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->tagAlbumsListingTag(),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumCategoryResource => $this->queryTags($user, $sorting),
			ttl: $ttl,
		);
	}

	private function queryTags(?User $user, AlbumSortingCriterion $sorting): AlbumCategoryResource
	{
		$query = $this->album_query_policy->applyVisibilityFilter(TagAlbum::query(), $user);
		(new SortingDecorator($query))->orderBy($sorting->column->fallbackForCategoryAlbumListing(), $sorting->order)->applyOrdering();

		$rows = $query
			->select(['tag_albums.id', 'base_albums.title', 'tag_albums.cover_id', 'base_albums.owner_id'])
			->toBase()
			->get();

		return $this->toCategoryResource($rows);
	}

	// ── GET /Albums/tags/rights ──────────────────────────────────────

	public function tagsRights(GetAlbumCategoryRequest $request): AlbumCategoryRightsResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$is_admin = $user?->may_administrate === true;

		$query = $this->album_query_policy->applyVisibilityFilter(TagAlbum::query(), $user);

		if ($is_admin) {
			$ids = $query->select(['tag_albums.id'])->toBase()->pluck('id')->all();
			$count = count($ids);

			return new AlbumCategoryRightsResource(
				ids: $ids,
				grants_edit: array_fill(0, $count, true),
				grants_download: array_fill(0, $count, true),
				grants_delete: array_fill(0, $count, true),
			);
		}

		$this->album_query_policy->joinSubComputedAccessPermissions($query, 'tag_albums.id', 'left', 'grants_', true, $user);

		// PostgreSQL has no `MAX()` aggregate for `boolean` (unlike MySQL/SQLite,
		// where a boolean is just an int); `bool_or()` is its equivalent.
		$or_aggregate = match (DB::getDriverName()) {
			'pgsql' => 'bool_or',
			default => 'MAX',
		};

		$rows = $query
			->select(['tag_albums.id'])
			->selectRaw($or_aggregate . '(grants_computed_access_permissions.grants_edit) as grants_edit')
			->selectRaw($or_aggregate . '(grants_computed_access_permissions.grants_download) as grants_download')
			->selectRaw($or_aggregate . '(grants_computed_access_permissions.grants_delete) as grants_delete')
			->groupBy('tag_albums.id')
			->toBase()
			->get();

		$ids = [];
		$grants_edit = [];
		$grants_download = [];
		$grants_delete = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$grants_edit[] = DbBool::parse($row->grants_edit);
			$grants_download[] = DbBool::parse($row->grants_download);
			$grants_delete[] = DbBool::parse($row->grants_delete);
		}

		return new AlbumCategoryRightsResource(ids: $ids, grants_edit: $grants_edit, grants_download: $grants_download, grants_delete: $grants_delete);
	}
}
