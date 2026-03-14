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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Handles `rating:` search tokens.
 *
 * Sub-modifiers:
 *   rating:avg:>=4  → WHERE photos.rating_avg >= 4
 *   rating:own:>=3  → WHERE EXISTS (photo_ratings WHERE user_id=:uid AND rating >= 3)
 *
 * The `own:` sub-modifier requires the user to be authenticated; an attempt
 * by an unauthenticated user results in a 422.
 *
 * Value range 0–5; operator required.
 */
class RatingStrategy implements PhotoSearchTokenStrategy
{
	public function apply(Builder $query, SearchToken $token): void
	{
		$operator = $token->operator;
		$value = (int) $token->value;

		if ($value < 0 || $value > 5) {
			throw ValidationException::withMessages(['term' => "Rating value must be between 0 and 5; got '{$token->value}'."]);
		}

		if ($operator === null) {
			throw ValidationException::withMessages(['term' => 'Rating token requires a comparison operator (e.g. rating:avg:>=4).']);
		}

		if ($token->sub_modifier === 'own') {
			if (!Auth::check()) {
				throw ValidationException::withMessages(['term' => 'rating:own: requires authentication.']);
			}

			$uid = Auth::id();
			$query->whereHas(
				'rating',
				fn (Builder $rq) => $rq->where('user_id', $uid)->where('rating', $operator, $value)
			);

			return;
		}

		// Default: avg
		$query->where('rating_avg', $operator, $value);
	}
}
