<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies;

use App\Contracts\Search\PhotoSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles `date:` search tokens.
 *
 * Exact:  `date:2024-05-01`    → WHERE DATE(taken_at) = '2024-05-01'
 * Range:  `date:>2024-01-01`   → WHERE taken_at > '2024-01-01 00:00:00'
 *         `date:<=2024-12-31`  → WHERE taken_at <= '2024-12-31 00:00:00'
 *
 * Multiple date: tokens are ANDed together by the dispatch loop.
 */
class DateStrategy implements PhotoSearchTokenStrategy
{
	public function apply(Builder $query, SearchToken $token): void
	{
		if ($token->operator === null) {
			// Exact calendar date.
			$query->whereDate('taken_at', '=', $token->value);
		} else {
			$query->where('taken_at', $token->operator, Carbon::parse($token->value));
		}
	}
}
