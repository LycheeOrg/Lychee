<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album\StructOfArrays;

use App\Actions\Album\StructOfArrays\Traits\GrantsAlbumRights;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Builder;

class QueryRightsForMatchingAlbums
{
	use GrantsAlbumRights;

	public function __construct(
		private readonly AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * @param Builder<Album> $query
	 */
	public function do(TagAlbum|PersonAlbum $album, Builder $query, ?User $user): AlbumRightsResource
	{
		$is_admin = $user?->may_administrate === true;

		// No single shared parent's access_permissions could uniformly
		// apply to a dynamically-matched, disparately-parented set.
		if ($is_admin) {
			return $this->allGranted($query, (string) $album->owner_id, false);
		}

		return $this->grantsResource($this->album_query_policy, $query, $user, (string) $album->owner_id, false);
	}

	public function emptyResource(TagAlbum|PersonAlbum $album): AlbumRightsResource
	{
		return new AlbumRightsResource(
			owner_id: (string) $album->owner_id,
			can_delete_children: false,
			can_move_children: false,
			ids: [],
			grants_edit: [],
			grants_download: [],
		);
	}
}
