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
 * Contract for photo-level search token strategies.
 *
 * Each strategy handles one modifier type and adds the appropriate
 * WHERE clause to the photo query builder.
 */
interface PhotoSearchTokenStrategy
{
	/**
	 * Apply the token's filter to the given query builder.
	 */
	public function apply(Builder $query, SearchToken $token): void;
}
