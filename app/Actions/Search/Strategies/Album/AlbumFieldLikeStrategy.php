<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies\Album;

use App\Actions\Search\Strategies\Traits\EscapesLikeWildcards;
use App\Contracts\Search\AlbumSearchTokenStrategy;
use App\DTO\Search\SearchToken;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
	use EscapesLikeWildcards;

	/**
	 * @param string|null $column       when null the strategy matches both title and description
	 * @param bool        $include_tags whether the plain-text fallback (only relevant when
	 *                                  `$column` is null) should also OR-in a match against the
	 *                                  album's own tags (Feature 050). Must be `false` for
	 *                                  {@link \App\Models\TagAlbum} queries (NFR-050-01).
	 */
	public function __construct(private readonly ?string $column = null, private readonly bool $include_tags = false)
	{
	}

	public function apply(Builder|QueryBuilder $query, SearchToken $token): void
	{
		$escaped = $this->escapeLike($token->value);
		$pattern = $token->is_prefix ? $escaped . '%' : '%' . $escaped . '%';

		if ($this->column !== null) {
			$query->whereRaw('base_albums.' . $this->column . " LIKE ? ESCAPE '!'", [$pattern]);
		} else {
			// Plain-text fallback: match title, description, and (Album only) tags.
			$query->where(function (Builder $q) use ($pattern): void {
				$q->whereRaw("base_albums.title LIKE ? ESCAPE '!'", [$pattern])
					->orWhereRaw("base_albums.description LIKE ? ESCAPE '!'", [$pattern]);
				if ($this->include_tags) {
					$q->orWhereHas('tags', fn (Builder $tq) => $tq->whereRaw("name LIKE ? ESCAPE '!'", [$pattern]));
				}
			});
		}
	}
}
