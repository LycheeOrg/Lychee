<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Assets\DbBool;
use App\Http\Requests\Gallery\AlbumListV3Request;
use App\Http\Resources\V3\AlbumListBulkEditFieldsResource;
use App\Http\Resources\V3\AlbumListResource;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Serves `GET /api/v3/Albums` (Feature 057, API-057-01): a lightweight,
 * rights-curated, unpaginated, cacheable flat album listing.
 */
class AlbumListController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	public function index(
		AlbumListV3Request $request,
	): AlbumListResource {
		/** @var User|null $user */
		$user = Auth::user();
		$with_parent_id = $request->withParentId();
		$for_bulk_edit = $request->forBulkEdit();

		$key = $this->cache_key_provider->albumListingV3Key($user?->id, $with_parent_id, $for_bulk_edit);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[$this->cache_key_provider->albumListingV3Tag()],
			fn (): AlbumListResource => $this->queryAlbumList($user, $with_parent_id, $for_bulk_edit),
			ttl: $ttl,
		);
	}

	private function queryAlbumList(?User $user, bool $with_parent_id, bool $for_bulk_edit): AlbumListResource
	{
		$rows = $this->queryAlbums($user, $with_parent_id, $for_bulk_edit);

		return $this->buildAlbumListResource($rows, $user, $with_parent_id, $for_bulk_edit);
	}

	private function queryAlbums(?User $user, bool $with_parent_id, bool $for_bulk_edit): Collection
	{
		$query = Album::query()->select([
			'albums.id',
			'base_albums.title',
			'albums._lft',
			'albums._rgt',
			'albums.cover_id',
			'albums.auto_cover_id_max_privilege',
			'albums.auto_cover_id_least_privilege',
			'base_albums.owner_id',
		]);

		if ($with_parent_id) {
			$query->addSelect('albums.parent_id');
		}

		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		if ($for_bulk_edit) {
			$query->leftJoin('users', 'users.id', '=', 'base_albums.owner_id');
			$this->album_query_policy->joinBaseAlbumBulkEditFields($query, 'albums.id', 'bulk_');
			$this->album_query_policy->joinBaseAlbumSensitive($query, 'albums.id', 'nsfw_');
			$this->album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', 'public_', true, null);

			$query->addSelect([
				'base_albums.created_at',
				'base_albums.description',
				DB::raw('COALESCE(users.display_name, users.username) as owner_name'),
				'bulk_base_albums.copyright',
				'bulk_base_albums.photo_layout',
				'bulk_base_albums.sorting_col as photo_sorting_col',
				'bulk_base_albums.sorting_order as photo_sorting_order',
				'bulk_base_albums.photo_timeline',
				'nsfw_base_albums.is_nsfw',
				'albums.license',
				'albums.album_sorting_col',
				'albums.album_sorting_order',
				'albums.album_thumb_aspect_ratio',
				'albums.album_timeline',
				'public_computed_access_permissions.base_album_id as public_base_album_id',
				'public_computed_access_permissions.is_link_required as public_is_link_required',
				'public_computed_access_permissions.grants_full_photo_access as public_grants_full_photo_access',
				'public_computed_access_permissions.grants_download as public_grants_download',
				'public_computed_access_permissions.grants_upload as public_grants_upload',
			]);
		}

		return $query->orderBy('albums._lft', 'asc')->toBase()->get();
	}

	private function buildAlbumListResource(
		Collection $rows,
		?User $user,
		bool $with_parent_id,
		bool $for_bulk_edit,
	): AlbumListResource {
		$resource = $this->toAlbumListResource($rows, $user);
		$resource->parent_ids = $with_parent_id ? $this->toParentIds($rows) : null;
		$resource->bulk_edit = $for_bulk_edit ? $this->toBulkEditResource($rows) : null;

		return $resource;
	}

	/**
	 * Create the light object.
	 *
	 * @param Collection<object{id:string,title:string,_lft:string,_rgt:string,cover_id:?string,auto_cover_id_max_privilege:?string,auto_cover_id_least_privilege:?string}> $rows
	 *
	 * @return AlbumListResource
	 */
	private function toAlbumListResource(Collection &$rows, ?User $user): AlbumListResource
	{
		$ids = [];
		$titles = [];
		$lft = [];
		$rgt = [];
		$cover_ids = [];

		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			$lft[] = (int) $row->_lft;
			$rgt[] = (int) $row->_rgt;
			$cover_ids[] = self::resolveCoverId($row, $user);
		}

		return new AlbumListResource(
			ids: $ids,
			titles: $titles,
			lft: $lft,
			rgt: $rgt,
			cover_ids: $cover_ids,
			parent_ids: null,
			bulk_edit: null,
		);
	}

	/**
	 * Create the light object.
	 *
	 * @param Collection<object{parent_id:?string}> $rows
	 *
	 * @return array<int,?string>
	 */
	private function toParentIds(Collection &$rows): array
	{
		$parent_ids = [];
		foreach ($rows as $row) {
			$parent_ids[] = $row->parent_id;
		}

		return $parent_ids;
	}

	/**
	 * Map to the Bulk edit resources.
	 *
	 * @param Collection<object{owner_id:string,owner_name:string,description:string,copyright:string,license:string,photo_layout:string,photo_sorting_col:string,photo_sorting_order:string,album_sorting_col:string,album_sorting_order:string,album_thumb_aspect_ratio:string,album_timeline:string,photo_timeline:string,is_nsfw:string,public_base_album_id:string,public_is_link_required:string,public_grants_full_photo_access:string,public_grants_download:string,public_grants_upload:string,created_at:string}> &$rows
	 *
	 * @return AlbumListBulkEditFieldsResource
	 */
	private function toBulkEditResource(Collection &$rows): AlbumListBulkEditFieldsResource
	{
		$owner_ids = [];
		$owner_names = [];
		$descriptions = [];
		$copyrights = [];
		$licenses = [];
		$photo_layouts = [];
		$photo_sorting_cols = [];
		$photo_sorting_orders = [];
		$album_sorting_cols = [];
		$album_sorting_orders = [];
		$album_thumb_aspect_ratios = [];
		$album_timelines = [];
		$photo_timelines = [];
		$is_nsfws = [];
		$is_publics = [];
		$is_link_requireds = [];
		$grants_full_photo_accesses = [];
		$grants_downloads = [];
		$grants_uploads = [];
		$created_ats = [];

		foreach ($rows as $row) {
			$owner_ids[] = (int) $row->owner_id;
			$owner_names[] = $row->owner_name;
			$descriptions[] = $row->description;
			$copyrights[] = $row->copyright;
			$licenses[] = $row->license;
			$photo_layouts[] = $row->photo_layout;
			$photo_sorting_cols[] = $row->photo_sorting_col;
			$photo_sorting_orders[] = $row->photo_sorting_order;
			$album_sorting_cols[] = $row->album_sorting_col;
			$album_sorting_orders[] = $row->album_sorting_order;
			$album_thumb_aspect_ratios[] = $row->album_thumb_aspect_ratio;
			$album_timelines[] = $row->album_timeline;
			$photo_timelines[] = $row->photo_timeline;
			$is_nsfws[] = DbBool::parse($row->is_nsfw);
			$is_publics[] = $row->public_base_album_id !== null;
			$is_link_requireds[] = DbBool::parse($row->public_is_link_required);
			$grants_full_photo_accesses[] = DbBool::parse($row->public_grants_full_photo_access);
			$grants_downloads[] = DbBool::parse($row->public_grants_download);
			$grants_uploads[] = DbBool::parse($row->public_grants_upload);
			$created_ats[] = $row->created_at; // Let's see if carbon is necessary
			// If not then that is way better for performance. If it is necessary, then we can use Carbon to convert the UTC time to ISO 8601 format.
			// $created_ats[] = Carbon::parse($row->created_at, 'UTC')->toIso8601String();
		}

		return new AlbumListBulkEditFieldsResource(
			owner_ids: $owner_ids,
			owner_names: $owner_names,
			descriptions: $descriptions,
			copyrights: $copyrights,
			licenses: $licenses,
			photo_layouts: $photo_layouts,
			photo_sorting_cols: $photo_sorting_cols,
			photo_sorting_orders: $photo_sorting_orders,
			album_sorting_cols: $album_sorting_cols,
			album_sorting_orders: $album_sorting_orders,
			album_thumb_aspect_ratios: $album_thumb_aspect_ratios,
			album_timelines: $album_timelines,
			photo_timelines: $photo_timelines,
			is_nsfws: $is_nsfws,
			is_publics: $is_publics,
			is_link_requireds: $is_link_requireds,
			grants_full_photo_accesses: $grants_full_photo_accesses,
			grants_downloads: $grants_downloads,
			grants_uploads: $grants_uploads,
			created_ats: $created_ats,
		);
	}

	/**
	 * Resolves the cover photo id for one raw album row, per FR-057-09's
	 * priority rule (mirrors {@see \App\Relations\HasAlbumThumb::getCoverTypeForAlbum()}):
	 * explicit `cover_id` first, else `auto_cover_id_max_privilege` for an
	 * admin/owner viewer, else `auto_cover_id_least_privilege`. Operates on
	 * already-selected columns only — no relation load, no extra query.
	 */
	public static function resolveCoverId(object $row, ?User $user): ?string
	{
		if ($row->cover_id !== null) {
			return $row->cover_id;
		}

		if ($user?->may_administrate === true || (int) $row->owner_id === $user?->id) {
			return $row->auto_cover_id_max_privilege;
		}

		return $row->auto_cover_id_least_privilege;
	}
}
