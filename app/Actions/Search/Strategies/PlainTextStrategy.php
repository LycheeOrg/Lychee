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
 * Handles plain-text search tokens (modifier = null).
 *
 * Matches the value against title, description, location, model, taken_at
 * and tags.name using LIKE '%value%'.
 * All fields are OR-ed within the group so that a single plain-text term
 * can match any of these columns.
 */
class PlainTextStrategy implements PhotoSearchTokenStrategy
{
	public function apply(Builder $query, SearchToken $token): void
	{
		$value = $this->escapeLike($token->value);

		$query->where(function (Builder $q) use ($value): void {
			$q->where('title', 'like', '%' . $value . '%')
				->orWhere('description', 'like', '%' . $value . '%')
				->orWhere('location', 'like', '%' . $value . '%')
				->orWhere('model', 'like', '%' . $value . '%')
				->orWhere('taken_at', 'like', '%' . $value . '%')
				->orWhereHas('tags', fn (Builder $tq) => $tq->where('name', 'like', '%' . $value . '%'));
		});
	}

	private function escapeLike(string $value): string
	{
		return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
	}
}
