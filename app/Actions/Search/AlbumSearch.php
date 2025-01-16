<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Search;

use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\AlbumSortingCriterion;
use App\Eloquent\FixedQueryBuilder;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\Builders\AlbumBuilder;
use App\Models\Builders\TagAlbumBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Models\TagAlbum;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Collection;

class AlbumSearch
{
	protected AlbumQueryPolicy $albumQueryPolicy;

	public function __construct(AlbumQueryPolicy $albumQueryPolicy)
	{
		$this->albumQueryPolicy = $albumQueryPolicy;
	}

	/**
	 * @param string[] $terms
	 *
	 * @return Collection<int,TagAlbum>
	 *
	 * @throws InternalLycheeException
	 */
	public function queryTagAlbums(array $terms): Collection
	{
		// Note: `applyVisibilityFilter` already adds a JOIN clause with `base_albums`.
		// No need to add a second JOIN clause.
		$albumQuery = $this->albumQueryPolicy->applyVisibilityFilter(
			TagAlbum::query()
		);
		$this->addSearchCondition($terms, $albumQuery);

		$sorting = AlbumSortingCriterion::createDefault();

		/** @phpstan-ignore-next-line */
		return (new SortingDecorator($albumQuery))
			->orderBy($sorting->column, $sorting->order)
			->get();
	}

	/**
	 * @param string[] $terms
	 *
	 * @return Collection<int,Album>
	 *
	 * @throws InternalLycheeException
	 */
	public function queryAlbums(array $terms): Collection
	{
		$albumQuery = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id');
		$this->addSearchCondition($terms, $albumQuery);
		$this->albumQueryPolicy->applyBrowsabilityFilter($albumQuery);

		$sorting = AlbumSortingCriterion::createDefault();

		return (new SortingDecorator($albumQuery))
			->orderBy($sorting->column, $sorting->order)
			->get();
	}

	/**
	 * Adds the search conditions to the provided query builder.
	 *
	 * @param string[]                                                                          $terms
	 * @param AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder<TagAlbum>|FixedQueryBuilder<Album> $query
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	private function addSearchCondition(array $terms, AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder $query): void
	{
		foreach ($terms as $term) {
			$query->where(
				fn (AlbumBuilder|TagAlbumBuilder|FixedQueryBuilder $query) => $query
					->where('base_albums.title', 'like', '%' . $term . '%')
					->orWhere('base_albums.description', 'like', '%' . $term . '%')
			);
		}
	}
}
