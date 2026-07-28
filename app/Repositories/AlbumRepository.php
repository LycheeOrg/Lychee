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
use App\Services\Cache\ManagedCacheService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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
		protected ManagedCacheService $managed_cache_service,
		protected ConfigManager $config_manager,
	) {
	}

	/**
	 * Get paginated child albums with all necessary relations eager-loaded.
	 *
	 * Wrapped in {@see ManagedCacheService::remember()} (FR-052-09): the cache
	 * key is scoped to the parent album, sorting, pagination, and requesting
	 * user; the entry is tagged with the parent's own tag (so any change that
	 * evicts the parent's tag, per FR-052-06, invalidates this listing) plus
	 * each returned child's own tag (so a future per-child invalidation, e.g.
	 * on rename, invalidates the listing too, even though nothing dispatches
	 * that yet).
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
		/** @var ?User $user */
		$user = Auth::user();
		$page = Paginator::resolveCurrentPage();
		$parent_tag = 'album:' . ($album_id ?? 'root');
		$key = sprintf(
			'children:%s:%s:%s:%d:%d:%s',
			$album_id ?? 'root',
			$sorting->column->value,
			$sorting->order->value,
			$per_page,
			$page,
			$user?->id ?? 'guest',
		);

		/** @var LengthAwarePaginator<Album> $result */
		$result = $this->managed_cache_service->remember(
			$key,
			[$parent_tag],
			$this->config_manager->getValueAsInt('managed_cache_ttl'),
			function () use ($album_id, $sorting, $per_page, $user): LengthAwarePaginator {
				// Build query for child albums
				$query = Album::query()
					->with(['owner'])
					->without(['thumb']) // Yes we do NOT want them yet.
					->where('parent_id', '=', $album_id);

				// Apply visibility filter
				$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

				// Apply sorting via SortingDecorator
				/** @var SortingDecorator<Album> */
				$sorting_decorator = new SortingDecorator($query);

				return $sorting_decorator
					->orderBy($sorting->column, $sorting->order)
					->paginate($per_page);
			},
		);

		$child_tags = array_map(
			static fn (Album $child): string => 'album:' . $child->id,
			$result->items(),
		);
		$this->managed_cache_service->addTags($key, $child_tags);

		return $result;
	}
}
