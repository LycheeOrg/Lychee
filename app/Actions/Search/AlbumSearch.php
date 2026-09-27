<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search;

use App\Actions\Search\Strategies\Album\AlbumDateStrategy;
use App\Actions\Search\Strategies\Album\AlbumFieldLikeStrategy;
use App\Actions\Search\Strategies\Album\AlbumTagStrategy;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Search\AlbumSearchTokenStrategy;
use App\DTO\AlbumSortingCriterion;
use App\DTO\Search\SearchToken;
use App\Eloquent\FixedQueryBuilder;
use App\Models\Album;
use App\Models\Builders\AlbumBuilder;
use App\Models\Builders\TagAlbumBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AlbumSearch
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * @param array<int,SearchToken> $tokens
	 *
	 * @return Collection<int,TagAlbum>
	 *
	 * @throws InternalLycheeException
	 */
	public function queryTagAlbums(array $tokens): Collection
	{
		$user = Auth::user();

		// Note: `applyVisibilityFilter` already adds a JOIN clause with `base_albums`.
		// No need to add a second JOIN clause.
		$album_query = $this->album_query_policy->applyVisibilityFilter(
			TagAlbum::query(),
			$user
		);
		// `include_tags: false` -- TagAlbum::tags() defines photo-matching
		// criteria, not album-level metadata, and must never be probed by
		// the album `tag:`/plain-text-tag strategies (NFR-050-01).
		$this->addSearchCondition($tokens, $album_query, include_tags: false);

		$sorting = AlbumSortingCriterion::createDefault();

		return (new SortingDecorator($album_query))
			->orderBy($sorting->column, $sorting->order)
			->get();
	}

	/**
	 * @param array<int,SearchToken>     $tokens
	 * @param Album|null                 $album   the optional top album which is used as a search base
	 * @param AlbumSortingCriterion|null $sorting overrides the site-wide default sort order (e.g. from the search page's sort control)
	 *
	 * @return Collection<int,Album>
	 *
	 * @throws InternalLycheeException
	 */
	public function queryAlbums(array $tokens, ?Album $album = null, ?AlbumSortingCriterion $sorting = null): Collection
	{
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$album_query = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->when($album !== null, fn ($q) => $q->where('albums._lft', '>=', $album->_lft)
				->where('albums._rgt', '<=', $album->_rgt));
		$this->addSearchCondition($tokens, $album_query, include_tags: true);
		$this->album_query_policy->applyBrowsabilityFilter($album_query, $user, $unlocked_album_ids);

		$sorting ??= AlbumSortingCriterion::createDefault();

		return (new SortingDecorator($album_query))
			->orderBy($sorting->column, $sorting->order)
			->get();
	}

	/**
	 * Builder-returning sibling of {@see self::queryAlbums()}, for Feature
	 * 069's v3 album tier — mirroring the `query()`/`sqlQuery()` pair
	 * {@see PhotoSearch} already exposes.
	 *
	 * It cannot simply reuse `queryAlbums()`: that method both materialises the
	 * result (`->get()`) and joins `base_albums` as a plain inner join on the
	 * real table, whereas {@see \App\Actions\Album\StructOfArrays\BuildAlbumDataResource}
	 * reads `computed_access_permissions.password` and the aliased `base_albums`
	 * sub-join. `applyBrowsabilityFilter()` — unlike `applyVisibilityFilter()` —
	 * never calls `prepareModelQueryOrFail()` and so joins neither.
	 *
	 * Both joins added here are LEFT joins that contribute no predicate, so the
	 * result **membership is identical to `queryAlbums()`'s**; only the
	 * available column set grows. Visibility filtering is deliberately *not*
	 * added on top: browsability already tests the target album itself, and
	 * layering an extra predicate would be a silent membership change inside a
	 * transport migration (spec.md NFR-069-06/07).
	 *
	 * @param array<int,SearchToken> $tokens
	 *
	 * @return AlbumBuilder
	 *
	 * @throws InternalLycheeException
	 */
	public function sqlQueryAlbums(array $tokens, ?Album $album = null): AlbumBuilder
	{
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$album_query = Album::query()
			->select(['albums.*'])
			->when($album !== null, fn ($q) => $q->where('albums._lft', '>=', $album->_lft)
				->where('albums._rgt', '<=', $album->_rgt));

		$this->album_query_policy->joinBaseAlbumOwnerId($album_query, 'albums.id');
		$this->album_query_policy->joinSubComputedAccessPermissions($album_query, 'albums.id', 'left', '', false, $user);

		$this->addSearchCondition($tokens, $album_query, include_tags: true);
		$this->album_query_policy->applyBrowsabilityFilter($album_query, $user, $unlocked_album_ids);

		return $album_query;
	}

	/**
	 * Adds the search conditions to the provided query builder.
	 *
	 * Only tokens whose modifier is recognised by the album layer (plain text,
	 * title, description, date) generate WHERE conditions.  Photo-only modifiers
	 * (rating, tag, type, ratio, make, lens, …) are silently skipped so they
	 * cannot accidentally match albums via the plain-text fallback.
	 *
	 * When every token in the query is photo-only (e.g. `rating:avg:>=3`), no
	 * album strategy is ever applied, and we force an empty result set so the
	 * caller does not return all albums.
	 *
	 * @param array<int,SearchToken>                                                            $tokens
	 * @param AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album> $query
	 * @param bool                                                                              $include_tags whether the `tag:`/plain-text-tag strategies apply
	 *                                                                                                        (Album only, never TagAlbum -- NFR-050-01)
	 */
	private function addSearchCondition(array $tokens, AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder $query, bool $include_tags): void
	{
		$strategies = $this->buildAlbumStrategyRegistry($include_tags);
		$applied = false;

		foreach ($tokens as $token) {
			// Tokens with a modifier unknown to albums (e.g. rating, tag, type)
			// are photo-only filters; skip them entirely.
			if ($token->modifier !== null && !array_key_exists($token->modifier, $strategies)) {
				continue;
			}
			$strategy = $strategies[$token->modifier ?? ''];
			$strategy->apply($query, $token);
			$applied = true;
		}

		// If every token was photo-only, return an empty set rather than all albums.
		if (!$applied) {
			$query->whereRaw('1 = 0');
		}
	}

	/**
	 * Build the map from modifier string (or empty string for plain text) to an album strategy instance.
	 *
	 * @param bool $include_tags whether to register the `tag:` modifier and extend the
	 *                           plain-text fallback to match album tags. Must be `false`
	 *                           for {@link TagAlbum} queries (NFR-050-01).
	 *
	 * @return array<string, AlbumSearchTokenStrategy>
	 */
	private function buildAlbumStrategyRegistry(bool $include_tags): array
	{
		$plain_text = new AlbumFieldLikeStrategy(null, $include_tags);

		$strategies = [
			'' => $plain_text,
			'title' => new AlbumFieldLikeStrategy('title'),
			'description' => new AlbumFieldLikeStrategy('description'),
			'date' => new AlbumDateStrategy(),
		];

		if ($include_tags) {
			$strategies['tag'] = new AlbumTagStrategy();
		}

		return $strategies;
	}
}
