<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\StructOfArrays;

use App\Actions\Photo\StructOfArrays\QueryPhotoDetails;
use App\DTO\Search\SearchToken;
use App\Http\Resources\V3\PhotoDetailResource;
use App\Models\Album;
use App\Models\User;

/**
 * Query logic for `GET /api/v3/Search/Photos/details` (Feature 069, FR-069-06).
 *
 * Contributes no projection of its own: the search predicate supplies the
 * candidate set, and {@see QueryPhotoDetails::fromQuery()} supplies the exact
 * same projection the per-album `details` tier uses. Reusing it rather than
 * copying it is what guarantees the two tiers cannot drift apart in shape, and
 * is why `fromQuery()` was added as a strictly additive sibling to that class's
 * existing `do()`.
 *
 * The search predicate doubles as the authorization filter here — an id the
 * caller cannot see simply does not survive it and is absent from the response,
 * never a 4xx (FR-069-06). The 300-id input cap is request validation, applied
 * one layer up.
 */
class QuerySearchPhotoDetails
{
	public function __construct(
		private readonly SearchPhotoSource $source,
		private readonly QueryPhotoDetails $query_photo_details,
	) {
	}

	/**
	 * @param array<int,SearchToken> $tokens
	 * @param string[]               $photo_ids
	 */
	public function do(array $tokens, ?Album $origin, ?User $user, array $photo_ids): PhotoDetailResource
	{
		$query = $this->source->query($tokens, $origin);

		return $this->query_photo_details->fromQuery($query, $user, $photo_ids);
	}
}
