<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Search;

use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\PhotoSortingCriterion;
use App\Eloquent\FixedQueryBuilder;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PhotoSearch
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
		protected PhotoQueryPolicy $photo_query_policy,
	) {
	}

	/**
	 * Apply search directly.
	 *
	 * @param array<int,string> $terms
	 *
	 * @return Collection<int,Photo> photos
	 *
	 * @throws InternalLycheeException
	 */
	public function query(array $terms): Collection
	{
		$query = $this->sqlQuery($terms);
		$sorting = PhotoSortingCriterion::createDefault();

		return (new SortingDecorator($query))
			->orderBy($sorting->column, $sorting->order)->get();
	}

	/**
	 * Create the query manually.
	 *
	 * @param array<int,string> $terms
	 * @param Album|null        $album the optional top album which is used as a search base
	 *
	 * @return FixedQueryBuilder<Photo>
	 */
	public function sqlQuery(array $terms, ?Album $album = null): Builder
	{
		$query = $this->photo_query_policy->applySearchabilityFilter(
			query: Photo::query()->with(['albums', 'statistics', 'size_variants', 'palette', 'tags', 'rating']),
			origin: $album,
			include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_search')
		);

		foreach ($terms as $term) {
			$query->where(
				fn (FixedQueryBuilder $query) => $query
					->where('title', 'like', '%' . $term . '%')
					->orWhere('description', 'like', '%' . $term . '%')
					// ->orWhere('tags', 'like', '%' . $term . '%')
					->orWhere('location', 'like', '%' . $term . '%')
					->orWhere('model', 'like', '%' . $term . '%')
					->orWhere('taken_at', 'like', '%' . $term . '%')
			);
		}

		return $query;
	}
}
