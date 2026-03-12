<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search;

use App\Actions\Search\Strategies\Album\AlbumDateStrategy;
use App\Actions\Search\Strategies\Album\AlbumFieldLikeStrategy;
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
		$this->addSearchCondition($tokens, $album_query);

		$sorting = AlbumSortingCriterion::createDefault();

		return (new SortingDecorator($album_query))
			->orderBy($sorting->column, $sorting->order)
			->get();
	}

	/**
	 * @param array<int,SearchToken> $tokens
	 * @param Album|null             $album  the optional top album which is used as a search base
	 *
	 * @return Collection<int,Album>
	 *
	 * @throws InternalLycheeException
	 */
	public function queryAlbums(array $tokens, ?Album $album = null): Collection
	{
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$album_query = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->when($album !== null, fn ($q) => $q->where('albums._lft', '>=', $album->_lft)
				->where('albums._rgt', '<=', $album->_rgt));
		$this->addSearchCondition($tokens, $album_query);
		$this->album_query_policy->applyBrowsabilityFilter($album_query, $user, $unlocked_album_ids);

		$sorting = AlbumSortingCriterion::createDefault();

		return (new SortingDecorator($album_query))
			->orderBy($sorting->column, $sorting->order)
			->get();
	}

	/**
	 * Adds the search conditions to the provided query builder.
	 *
	 * @param array<int,SearchToken>                                                            $tokens
	 * @param AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album> $query
	 */
	private function addSearchCondition(array $tokens, AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder $query): void
	{
		$strategies = $this->buildAlbumStrategyRegistry();

		foreach ($tokens as $token) {
			$strategy = $strategies[$token->modifier ?? ''] ?? $strategies[''];
			$strategy->apply($query, $token);
		}
	}

	/**
	 * Build the map from modifier string (or empty string for plain text) to an album strategy instance.
	 *
	 * @return array<string, AlbumSearchTokenStrategy>
	 */
	private function buildAlbumStrategyRegistry(): array
	{
		$plain_text = new AlbumFieldLikeStrategy(null);

		return [
			'' => $plain_text,
			'title' => new AlbumFieldLikeStrategy('title'),
			'description' => new AlbumFieldLikeStrategy('description'),
			'date' => new AlbumDateStrategy(),
		];
	}
}
