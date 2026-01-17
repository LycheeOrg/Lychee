<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Smart album containing the top N highest-rated photos.
 * Photos with the same rating as the Nth photo are included (ties).
 * Requires Lychee SE license.
 */
class BestPicturesAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::BEST_PICTURES->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		// The condition filters for photos that have a rating (not NULL)
		// The tie-inclusion logic is handled in getPhotosAttribute()
		parent::__construct(
			id: SmartAlbumType::BEST_PICTURES,
			smart_condition: fn (Builder $q) => $q->whereNotNull('photos.rating_avg')
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Override to implement tie-inclusion logic for top N photos.
	 *
	 * @return LengthAwarePaginator<int,Photo>
	 *
	 * @throws InvalidOrderDirectionException
	 * @throws InvalidQueryModelException
	 */
	protected function getPhotosAttribute(): LengthAwarePaginator
	{
		if ($this->photos !== null) {
			return $this->photos;
		}

		$limit = $this->config_manager->getValueAsInt('best_pictures_count');

		// Get the Nth photo's rating to determine the cutoff
		$cutoff_rating = $this->getCutoffRating($limit);

		if ($cutoff_rating === null) {
			// No photos with ratings, return empty paginator
			$this->photos = new LengthAwarePaginator([], 0, $limit);

			return $this->photos;
		}

		// Include all photos with rating >= cutoff (this handles ties)
		$query = $this->photos()->where('photos.rating_avg', '>=', $cutoff_rating);

		// Always sort by rating DESC for Best Pictures
		$sorting = new PhotoSortingCriterion(
			column: ColumnSortingType::RATING_AVG,
			order: OrderSortingType::DESC
		);

		/** @var LengthAwarePaginator<int,Photo> $photos */
		$photos = (new SortingDecorator($query))
			->orderPhotosBy($sorting->column, $sorting->order)
			->paginate($this->config_manager->getValueAsInt('photos_pagination_limit'));

		$this->photos = $photos;

		return $this->photos;
	}

	/**
	 * Get the rating of the Nth photo to use as the cutoff.
	 *
	 * @param int $limit the N for "top N photos"
	 *
	 * @return string|null the rating at position N, or null if fewer than N rated photos exist
	 */
	private function getCutoffRating(int $limit): ?string
	{
		// Get the Nth highest-rated photo
		$nth_photo = $this->photos()
			->whereNotNull('photos.rating_avg')
			->orderByRaw('COALESCE(photos.rating_avg, -1) DESC')
			->skip($limit - 1)
			->take(1)
			->first();

		if ($nth_photo === null) {
			// Fewer than N photos with ratings exist
			// Get the lowest rating among existing rated photos
			$lowest_rated = $this->photos()
				->whereNotNull('photos.rating_avg')
				->orderByRaw('COALESCE(photos.rating_avg, -1) ASC')
				->first();

			return $lowest_rated?->rating_avg;
		}

		return $nth_photo->rating_avg;
	}
}
