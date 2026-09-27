<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\StructOfArrays;

use App\Actions\Album\StructOfArrays\BuildAlbumDataResource;
use App\Actions\Search\AlbumSearch;
use App\DTO\AlbumSortingCriterion;
use App\DTO\Search\SearchToken;
use App\Http\Resources\V3\AlbumDataResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\User;

/**
 * Query logic for `GET /api/v3/Search/albums` (Feature 069, FR-069-07).
 *
 * Contributes no projection of its own: {@see BuildAlbumDataResource} already
 * accepts an arbitrary pre-filtered album query, so this tier is just "build
 * the right query, hand it over". That is the whole reason Q-069-03 chose to
 * reuse {@see AlbumDataResource} rather than invent a search-specific album
 * shape — it also means the frontend's existing `adaptAlbumChildTile.ts` works
 * against this endpoint unmodified.
 *
 * Unpaginated and uncapped, matching v2's own album behaviour exactly; only the
 * photo half carries a result cap (spec.md FR-069-02), because only the photo
 * half can realistically run away.
 */
class QuerySearchAlbums
{
	public function __construct(
		private readonly AlbumSearch $album_search,
		private readonly BuildAlbumDataResource $build_album_data_resource,
	) {
	}

	/**
	 * @param array<int,SearchToken> $tokens
	 */
	public function do(array $tokens, ?Album $origin, ?User $user, ?AlbumSortingCriterion $sorting): AlbumDataResource
	{
		$query = $this->album_search->sqlQueryAlbums($tokens, $origin);

		$sorting ??= AlbumSortingCriterion::createDefault();
		(new SortingDecorator($query))->orderBy($sorting->column, $sorting->order)->applyOrdering();

		return $this->build_album_data_resource->do($query, $user);
	}
}
