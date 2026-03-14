<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies\Album;

use App\Contracts\Search\AlbumSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles `date:` tokens in album searches.
 *
 * Operates on `base_albums.created_at`.
 *
 * Exact:  date:2024-05-01   → WHERE DATE(base_albums.created_at) = '2024-05-01'
 * Range:  date:>2024-01-01  → WHERE base_albums.created_at > '2024-01-01 00:00:00'
 */
class AlbumDateStrategy implements AlbumSearchTokenStrategy
{
	public function apply(Builder $query, SearchToken $token): void
	{
		if ($token->operator === null) {
			$query->whereDate('base_albums.created_at', '=', $token->value);
		} else {
			$query->where('base_albums.created_at', $token->operator, Carbon::parse($token->value));
		}
	}
}
