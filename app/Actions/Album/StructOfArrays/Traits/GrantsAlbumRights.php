<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays\Traits;

use App\Assets\DbBool;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Shared plumbing for the two `/rights` query variants (a real Album's
 * direct children, or a TagAlbum/PersonAlbum's dynamically-matched set) —
 * both shapes are byte-for-byte identical past "here is the already-visibility-filtered
 * query", differing only in whether `can_delete_children`/`can_move_children`
 * is a real per-request check (Album) or always `false` (no single shared
 * parent's `access_permissions` could uniformly apply to a dynamically-matched,
 * disparately-parented set).
 *
 * Takes {@see AlbumQueryPolicy} as an explicit parameter rather than reading
 * it off `$this` — a trait has no constructor of its own, and reaching into
 * a property it never declares would make the using class's dependency
 * invisible from here.
 */
trait GrantsAlbumRights
{
	/**
	 * Mirrors `AlbumQueryPolicy::applyVisibilityFilter()`/`applyReachabilityFilter()`'s
	 * own admin early-return — neither the grants join nor any
	 * `can_delete_children` check ever runs.
	 *
	 * @param Builder<Album> $query
	 */
	final protected function allGranted(Builder $query, string $owner_id, bool $can_delete_children): AlbumRightsResource
	{
		$ids = $query->select(['albums.id'])->toBase()->pluck('id')->all();
		$count = count($ids);

		return new AlbumRightsResource(
			owner_id: $owner_id,
			can_delete_children: $can_delete_children,
			can_move_children: $can_delete_children,
			ids: $ids,
			grants_edit: array_fill(0, $count, true),
			grants_download: array_fill(0, $count, true),
		);
	}

	/**
	 * `getComputedAccessPermissionSubQuery(full: true, ...)` applies no
	 * internal `GROUP BY` — a caller in multiple groups with separate
	 * matching grants on the same child would otherwise produce duplicate
	 * joined rows. `GROUP BY` + `MAX()`/`bool_or()` here correctly OR-merges
	 * them: any matching group/user/public row granting a right is enough.
	 *
	 * @param Builder<Album> $query
	 */
	final protected function grantsResource(AlbumQueryPolicy $album_query_policy, Builder $query, ?User $user, string $owner_id, bool $can_delete_children): AlbumRightsResource
	{
		$album_query_policy->joinSubComputedAccessPermissions($query, 'albums.id', 'left', 'grants_', true, $user);

		// PostgreSQL has no `MAX()` aggregate for `boolean` (unlike MySQL/SQLite,
		// where a boolean is just an int); `bool_or()` is its equivalent.
		$or_aggregate = match (DB::getDriverName()) {
			'pgsql' => 'bool_or',
			default => 'MAX',
		};

		$rows = $query
			->select(['albums.id'])
			->selectRaw($or_aggregate . '(grants_computed_access_permissions.grants_edit) as grants_edit')
			->selectRaw($or_aggregate . '(grants_computed_access_permissions.grants_download) as grants_download')
			->groupBy('albums.id')
			->toBase()
			->get();

		$ids = [];
		$grants_edit = [];
		$grants_download = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$grants_edit[] = DbBool::parse($row->grants_edit);
			$grants_download[] = DbBool::parse($row->grants_download);
		}

		return new AlbumRightsResource(
			owner_id: $owner_id,
			can_delete_children: $can_delete_children,
			can_move_children: $can_delete_children,
			ids: $ids,
			grants_edit: $grants_edit,
			grants_download: $grants_download,
		);
	}
}
