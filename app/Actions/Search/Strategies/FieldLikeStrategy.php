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
 * Generic strategy that performs a LIKE match on a single photos column.
 *
 * Instantiate once per modifier in the strategy registry:
 *   'make' => new FieldLikeStrategy('make')
 *
 * Without trailing *:  WHERE photos.<column> LIKE '%value%'
 * With trailing *:     WHERE photos.<column> LIKE 'value%'
 */
class FieldLikeStrategy implements PhotoSearchTokenStrategy
{
	public function __construct(private readonly string $column)
	{
	}

	public function apply(Builder $query, SearchToken $token): void
	{
		$escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $token->value);

		if ($token->is_prefix) {
			$query->whereRaw("{$this->column} LIKE ? ESCAPE '!'", [$escaped . '%']);
		} else {
			$query->whereRaw("{$this->column} LIKE ? ESCAPE '!'", ['%' . $escaped . '%']);
		}
	}
}
