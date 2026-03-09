<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Repositories;

use App\Constants\PhotoAlbum as PA;
use App\DTO\PhotoSortingCriterion;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repository for Photo queries.
 *
 * Centralizes photo query logic including eager loading and sorting.
 */
class PhotoRepository
{
	/**
	 * Get paginated photos for an album with all necessary relations eager-loaded.
	 *
	 * Eager loads: size_variants, tags, palette, statistics, rating
	 * These relations are required by PhotoResource to avoid N+1 queries.
	 *
	 * @param string                $album_id the album ID to get photos from
	 * @param PhotoSortingCriterion $sorting  the sorting criteria
	 * @param int                   $per_page number of photos per page
	 *
	 * @return LengthAwarePaginator<Photo>
	 *
	 * @throws \App\Exceptions\Internal\InvalidOrderDirectionException
	 */
	/**
	 * Get paginated photos for an album with optional tag filtering.
	 *
	 * @param string                $album_id  the album ID
	 * @param PhotoSortingCriterion $sorting   the sorting criterion
	 * @param int                   $per_page  photos per page
	 * @param array<int>|null       $tag_ids   optional tag IDs to filter by
	 * @param string                $tag_logic 'OR' (any tag) or 'AND' (all tags), default 'OR'
	 *
	 * @return LengthAwarePaginator<int,Photo>
	 */
	public function getPhotosForAlbumPaginated(
		string $album_id,
		PhotoSortingCriterion $sorting,
		int $per_page,
		?array $tag_ids = null,
		string $tag_logic = 'OR',
	): LengthAwarePaginator {
		// Build query for photos belonging to the album via the photo_album pivot table
		$query = Photo::query()
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, '=', $album_id)
			->select('photos.*')
			->with(['size_variants', 'tags', 'palette', 'statistics', 'rating']);

		// Apply tag filtering if tag_ids provided and not empty
		if ($tag_ids !== null && count($tag_ids) > 0) {
			$this->applyTagFilter($query, $tag_ids, $tag_logic);
		}

		// Apply sorting via SortingDecorator
		/** @var SortingDecorator<Photo> */
		$sorting_decorator = new SortingDecorator($query);

		return $sorting_decorator
			->orderPhotosBy($sorting->column, $sorting->order)
			->paginate($per_page);
	}

	/**
	 * Apply tag filtering to photo query.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder<Photo> $query     the query builder
	 * @param array<int>                                   $tag_ids   tag IDs to filter by
	 * @param string                                       $tag_logic 'OR' or 'AND'
	 */
	private function applyTagFilter($query, array $tag_ids, string $tag_logic): void
	{
		if ($tag_logic === 'AND' && count($tag_ids) > 1) {
			// AND logic: photo must have ALL specified tags
			// Use join + groupBy + having to ensure all tags are present
			$query
				->join('photos_tags as pt', 'photos.id', '=', 'pt.photo_id')
				->whereIn('pt.tag_id', $tag_ids)
				->groupBy('photos.id')
				->havingRaw('COUNT(DISTINCT pt.tag_id) = ?', [count($tag_ids)]);
		} else {
			// OR logic (or single tag): photo must have ANY of the specified tags
			$query->whereHas('tags', function ($q) use ($tag_ids): void {
				$q->whereIn('tags.id', $tag_ids);
			});
		}
	}
}
