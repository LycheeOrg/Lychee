<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies;

use App\Actions\Search\Strategies\Traits\EscapesLikeWildcards;
use App\Contracts\Search\PhotoSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles plain-text search tokens (modifier = null).
 *
 * Matches the value against title, description, location, model
 * and tags.name using LIKE '%value%'.
 * All fields are OR-ed within the group so that a single plain-text term
 * can match any of these columns.
 */
class PlainTextStrategy implements PhotoSearchTokenStrategy
{
	use EscapesLikeWildcards;

	public function apply(Builder $query, SearchToken $token): void
	{
		$value = $this->escapeLike($token->value);
		$pattern = '%' . $value . '%';

		$query->where(function (Builder $q) use ($pattern): void {
			$q->whereRaw("title LIKE ? ESCAPE '!'", [$pattern])
				->orWhereRaw("description LIKE ? ESCAPE '!'", [$pattern])
				->orWhereRaw("location LIKE ? ESCAPE '!'", [$pattern])
				->orWhereRaw("model LIKE ? ESCAPE '!'", [$pattern])
				->orWhereHas('tags', fn (Builder $tq) => $tq->whereRaw("name LIKE ? ESCAPE '!'", [$pattern]));
		});
	}
}
