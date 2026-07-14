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

/**
 * Handles `tag:` search tokens for regular albums (Feature 050 - Album Tags).
 *
 * Exact match:  `tag:vacation`  -> WHERE EXISTS (albums_tags.tags.name = 'vacation')
 * Prefix match: `tag:vac*`      -> WHERE EXISTS (albums_tags.tags.name LIKE 'vac%')
 *
 * This strategy must only ever be registered for {@link \App\Models\Album}
 * queries (i.e. {@link \App\Actions\Search\AlbumSearch::queryAlbums()}),
 * never for {@link \App\Models\TagAlbum} queries: `TagAlbum::tags()` is a
 * distinct relation with distinct semantics (photo-matching criteria, not
 * album-level metadata) and must never be probed by this strategy
 * (NFR-050-01).
 */
class AlbumTagStrategy implements AlbumSearchTokenStrategy
{
	use EscapesLikeWildcards;

	public function apply(Builder $query, SearchToken $token): void
	{
		$value = $token->value;

		if ($token->is_prefix) {
			$pattern = $this->escapeLike($value) . '%';
			$query->whereHas('tags', fn (Builder $tq) => $tq->whereRaw("LOWER(name) LIKE LOWER(?) ESCAPE '!'", [$pattern]));
		} else {
			$query->whereHas('tags', fn (Builder $tq) => $tq->whereRaw('LOWER(name) = LOWER(?)', [$value]));
		}
	}
}
