<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies\Album;

use App\Contracts\Search\AlbumSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles `title:` and `description:` tokens in album searches.
 *
 * Without trailing *:  WHERE base_albums.<column> LIKE '%value%'
 * With trailing *:     WHERE base_albums.<column> LIKE 'value%'
 *
 * Also used as the fallback plain-text strategy for albums
 * (modifier=null tokens search both title and description with OR).
 */
class AlbumFieldLikeStrategy implements AlbumSearchTokenStrategy
{
	/**
	 * @param string|null $column when null the strategy matches both title and description
	 */
	public function __construct(private readonly ?string $column = null)
	{
	}

	public function apply(Builder $query, SearchToken $token): void
	{
		$escaped = $this->escapeLike($token->value);
		$pattern = $token->is_prefix ? $escaped . '%' : '%' . $escaped . '%';

		if ($this->column !== null) {
			$query->whereRaw('base_albums.' . $this->column . " LIKE ? ESCAPE '!'", [$pattern]);
		} else {
			// Plain-text fallback: match either title or description.
			$query->where(function (Builder $q) use ($pattern): void {
				$q->whereRaw("base_albums.title LIKE ? ESCAPE '!'", [$pattern])
					->orWhereRaw("base_albums.description LIKE ? ESCAPE '!'", [$pattern]);
			});
		}
	}

	private function escapeLike(string $value): string
	{
		return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
	}
}
