<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\StructOfArrays;

use App\Actions\Album\StructOfArrays\Traits\GrantsAlbumRights;
use App\Actions\Search\AlbumSearch;
use App\DTO\Search\SearchToken;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Spatie\LaravelData\Optional;

/**
 * Query logic for `GET /api/v3/Search/albums/rights` (Feature 069, FR-069-08).
 *
 * A search result set is disparately parented by construction, which is exactly
 * the case {@see \App\Actions\Album\StructOfArrays\QueryRightsForMatchingAlbums}
 * already handles for `TagAlbum`/`PersonAlbum`: no single parent's
 * `access_permissions` row could uniformly apply, so
 * `can_delete_children`/`can_move_children` are always `false` (FR-069-09).
 *
 * It goes one step further than that precedent: a tag/person album at least has
 * its *own* `owner_id` to report, whereas a search has no owning album at all,
 * so `owner_id` is omitted from the payload entirely — the same resolution
 * `/Albums/root/rights` reached for root (Q-062-16).
 */
class QuerySearchAlbumRights
{
	use GrantsAlbumRights;

	public function __construct(
		private readonly AlbumSearch $album_search,
		private readonly AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * @param array<int,SearchToken> $tokens
	 */
	public function do(array $tokens, ?Album $origin, ?User $user): AlbumRightsResource
	{
		$query = $this->album_search->sqlQueryAlbums($tokens, $origin);

		if ($user?->may_administrate === true) {
			return $this->allGranted($query, Optional::create(), false, false);
		}

		return $this->grantsResource($this->album_query_policy, $query, $user, Optional::create(), false, false);
	}
}
