<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Search;

use App\DTO\Search\SearchToken;
use Illuminate\Database\Eloquent\Builder;

/**
 * Contract for album-level search token strategies.
 *
 * Mirrors {@link PhotoSearchTokenStrategy} for album queries.
 */
interface AlbumSearchTokenStrategy
{
	/**
	 * Apply the token's filter to the given album query builder.
	 */
	public function apply(Builder $query, SearchToken $token): void;
}
