<?php

/**
 * SPDX-License-Identifier: MIT
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

	public function __construct(AlbumQueryPolicy $album_query_policy)
	{
		$this->albumQueryPolicy = $album_query_policy;
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
		$album_query = $this->albumQueryPolicy->applyVisibilityFilter(
			TagAlbum::query()
		);
		$this->addSearchCondition($terms, $album_query);

		$sorting = AlbumSortingCriterion::createDefault();

		return (new SortingDecorator($album_query))
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
		$album_query = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id');
		$this->addSearchCondition($terms, $album_query);
		$this->albumQueryPolicy->applyBrowsabilityFilter($album_query);

		$sorting = AlbumSortingCriterion::createDefault();

		return (new SortingDecorator($album_query))
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
