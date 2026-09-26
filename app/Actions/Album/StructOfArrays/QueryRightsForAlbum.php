<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\Actions\Album\StructOfArrays\Traits\GrantsAlbumRights;
use App\Constants\AccessPermissionConstants as APC;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;

class QueryRightsForAlbum
{
	use GrantsAlbumRights;

	public function __construct(
		private readonly AlbumQueryPolicy $album_query_policy,
	) {
	}

	public function do(Album $album, ?User $user): AlbumRightsResource
	{
		$is_admin = $user?->may_administrate === true;

		$query = Album::query()->where('parent_id', '=', $album->id);
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		if ($is_admin) {
			return $this->allGranted($query, (string) $album->owner_id, true, true);
		}

		$can_delete_children = $this->parentGrants($album, $user, APC::GRANTS_DELETE);
		// A child is content of this album, so moving it
		// follows the move grant on this album, like delete.
		$can_move_children = $this->parentGrants($album, $user, APC::GRANTS_MOVE);

		return $this->grantsResource($this->album_query_policy, $query, $user, (string) $album->owner_id, $can_delete_children, $can_move_children);
	}

	/**
	 * Mirrors {@see \App\Policies\AlbumPolicy::canDelete()}'s (and
	 * {@see \App\Policies\AlbumPolicy::canMoveAlbum()}'s) parent-scoped
	 * `AccessPermission` query verbatim, addressed directly at `$album->id`
	 * (equivalent to `$abstract_album->parent_id` there, since every
	 * returned child's parent *is* `$album->id`).
	 *
	 * @param string $grant APC::GRANTS_DELETE or APC::GRANTS_MOVE
	 */
	private function parentGrants(Album $album, ?User $user, string $grant): bool
	{
		if ($user === null) {
			return false;
		}

		return AccessPermission::query()
			->where(APC::BASE_ALBUM_ID, '=', $album->id)
			->where(
				fn ($query) => $query->where(APC::USER_ID, '=', $user->id)
					->orWhereIn(APC::USER_GROUP_ID, $user->user_groups->pluck('id'))
			)
			->where($grant, '=', true)
			->exists();
	}
}
