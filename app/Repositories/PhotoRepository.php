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
	public function getPhotosForAlbumPaginated(
		string $album_id,
		PhotoSortingCriterion $sorting,
		int $per_page,
	): LengthAwarePaginator {
		// Build query for photos belonging to the album via the photo_album pivot table
		$query = Photo::query()
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, '=', $album_id)
			->select('photos.*')
			->with(['size_variants', 'tags', 'palette', 'statistics', 'rating']);

		// Apply sorting via SortingDecorator
		/** @var SortingDecorator<Photo> */
		$sorting_decorator = new SortingDecorator($query);

		return $sorting_decorator
			->orderPhotosBy($sorting->column, $sorting->order)
			->paginate($per_page);
	}
}
