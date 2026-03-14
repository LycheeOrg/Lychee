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
 * Handles `tag:` search tokens.
 *
 * Exact match:  `tag:sunset`  → WHERE EXISTS (tags.name = 'sunset')
 * Prefix match: `tag:sun*`    → WHERE EXISTS (tags.name LIKE 'sun%')
 *
 * Uses whereHas (EXISTS subquery) to avoid duplicate photo rows when a photo
 * has multiple matching tags (NFR-027-02).
 */
class TagStrategy implements PhotoSearchTokenStrategy
{
	public function apply(Builder $query, SearchToken $token): void
	{
		$value = $token->value;

		if ($token->is_prefix) {
			$escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
			$query->whereHas('tags', fn (Builder $tq) => $tq->whereRaw('LOWER(name) LIKE LOWER(?)', [$escaped . '%']));
		} else {
			$query->whereHas('tags', fn (Builder $tq) => $tq->whereRaw('LOWER(name) = LOWER(?)', [$value]));
		}
	}
}
