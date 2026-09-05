<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\Assets\DbBool;
use App\Http\Controllers\Gallery\AlbumListController;
use App\Http\Resources\V3\AlbumDataResource;
use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds the struct-of-arrays {@see AlbumDataResource} from an
 * already-filtered/ordered album query — shared by every `/children`-tier
 * caller (a real Album's direct children, or a TagAlbum/PersonAlbum's
 * dynamically-matched set) regardless of how that query was constructed.
 *
 * @phpstan-type TRow object{id:string,title:string,description:?string,cover_id:?string,auto_cover_id_max_privilege:?string,auto_cover_id_least_privilege:?string,owner_id:int,bucket_id:?string,password:?string,is_nsfw:mixed,is_pinned:mixed,public_grant_id:?string,public_is_link_required:mixed,num_children:int|string,num_photos:int|string,created_at:string,min_taken_at:?string,max_taken_at:?string}
 */
class BuildAlbumDataResource
{
	/**
	 * @param Builder<Album> $query
	 */
	public function do(Builder $query, ?User $user): AlbumDataResource
	{
		// The album's own public/anonymous grant,
		// independent of the requesting viewer — distinct from
		// computed_access_permissions above, which reflects the *viewer's*
		// effective access. A unique index on
		// (base_album_id, user_id_unique_key, user_group_id_unique_key)
		// guarantees at most one such row per album, so this left join can
		// never fan out the result set. Joined via a narrow-column subquery
		// (mirrors joinBaseAlbumOwnerId()/joinSubComputedAccessPermissions()'s
		// own convention), not a raw table join — access_permissions carries
		// its own created_at/updated_at columns that would otherwise collide
		// with SortingDecorator's unqualified `ORDER BY created_at`.
		$query->joinSub(
			query: DB::table('access_permissions')
				->select(['base_album_id', 'is_link_required'])
				->whereNull('user_id')
				->whereNull('user_group_id'),
			as: 'public_access_permissions',
			first: 'public_access_permissions.base_album_id',
			operator: '=',
			second: 'albums.id',
			type: 'left'
		);

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
				'base_albums.is_pinned',
				'public_access_permissions.base_album_id as public_grant_id',
				'public_access_permissions.is_link_required as public_is_link_required',
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
	 * Shortcut for callers that already know the result is empty (e.g. a
	 * TagAlbum/PersonAlbum listing disabled by config) — skips the query
	 * entirely rather than running one that would return zero rows.
	 */
	public function empty(?User $user): AlbumDataResource
	{
		return $this->toResource(collect(), $user);
	}

	/**
	 * @param Collection<int,TRow> $rows
	 */
	private function toResource(Collection $rows, ?User $user): AlbumDataResource
	{
		$ids = [];
		$titles = [];
		$descriptions = [];
		$cover_ids = [];
		$bucket_ids = [];
		$owner_ids = [];
		$is_password_requireds = [];
		$is_nsfws = [];
		$is_pinneds = [];
		$is_publics = [];
		$is_link_requireds = [];
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
			$owner_ids[] = (string) $row->owner_id;
			$is_password_requireds[] = $row->password !== null;
			$is_nsfws[] = DbBool::parse($row->is_nsfw);
			$is_pinneds[] = DbBool::parse($row->is_pinned);
			$is_publics[] = $row->public_grant_id !== null;
			$is_link_requireds[] = DbBool::parse($row->public_is_link_required);
			$has_subalbums[] = ((int) $row->num_children) > 0;
			$num_photos[] = (int) $row->num_photos;
			$num_subalbums[] = (int) $row->num_children;
			$created_ats[] = $row->created_at;
			$min_taken_ats[] = $row->min_taken_at;
			$max_taken_ats[] = $row->max_taken_at;
		}

		return new AlbumDataResource(
			ids: $ids,
			titles: $titles,
			descriptions: $descriptions,
			cover_ids: $cover_ids,
			bucket_ids: $bucket_ids,
			owner_ids: $owner_ids,
			is_password_requireds: $is_password_requireds,
			is_nsfws: $is_nsfws,
			is_pinneds: $is_pinneds,
			is_publics: $is_publics,
			is_link_requireds: $is_link_requireds,
			has_subalbums: $has_subalbums,
			num_photos: $num_photos,
			num_subalbums: $num_subalbums,
			created_ats: $created_ats,
			min_taken_ats: $min_taken_ats,
			max_taken_ats: $max_taken_ats,
		);
	}
}
