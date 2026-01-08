<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Repositories;

use App\DTO\AlbumSortingCriterion;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Repository for Album queries.
 *
 * Centralizes album query logic including eager loading and sorting.
 */
class AlbumRepository
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
	) {
	}

	/**
	 * Get paginated child albums with all necessary relations eager-loaded.
	 *
	 * @param string|null           $album_id the parent album ID (null for root albums)
	 * @param AlbumSortingCriterion $sorting  the sorting criteria
	 * @param int                   $per_page number of albums per page
	 *
	 * @return LengthAwarePaginator<Album>
	 *
	 * @throws \App\Exceptions\Internal\InvalidOrderDirectionException
	 * @throws \App\Contracts\Exceptions\InternalLycheeException
	 */
	public function getChildrenPaginated(
		?string $album_id,
		AlbumSortingCriterion $sorting,
		int $per_page,
	): LengthAwarePaginator {
		// Build query for child albums
		$query = Album::query()
			->where('parent_id', '=', $album_id);

		// Apply visibility filter
		/** @var ?User $user */
		$user = Auth::user();
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		// Apply sorting via SortingDecorator
		/** @var SortingDecorator<Album> */
		$sorting_decorator = new SortingDecorator($query);

		return $sorting_decorator
			->orderBy($sorting->column, $sorting->order)
			->paginate($per_page);
	}
}
