<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies;

use App\Contracts\Search\PhotoSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles `type:` search tokens.
 *
 * Matches the MIME type column with a LIKE '%value%' clause.
 * Example:  type:jpeg  → WHERE photos.type LIKE '%jpeg%'
 */
class TypeStrategy implements PhotoSearchTokenStrategy
{
	public function apply(Builder $query, SearchToken $token): void
	{
		$escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $token->value);
		$query->where('type', 'like', '%' . $escaped . '%');
	}
}
