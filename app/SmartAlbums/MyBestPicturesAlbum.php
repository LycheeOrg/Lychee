<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Smart album containing the top N highest-rated photos by the current user.
 * Photos with the same rating as the Nth photo are included (ties).
 * Only visible to authenticated users with Lychee SE license.
 */
class MyBestPicturesAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::MY_BEST_PICTURES->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		// The condition filters for photos that have been rated by current user
		// The tie-inclusion logic is handled in getPhotosAttribute()
		parent::__construct(
			id: SmartAlbumType::MY_BEST_PICTURES,
			smart_condition: fn (Builder $q) => $q->whereHas('ratings', function ($query): void {
				$query->where('user_id', '=', Auth::id() ?? 0);
			})
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Override to implement tie-inclusion logic for top N photos rated by current user.
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

		$limit = $this->config_manager->getValueAsInt('my_best_pictures_count');

		// Get the Nth photo's rating to determine the cutoff
		$cutoff_rating = $this->getCutoffRating($limit);

		if ($cutoff_rating === null) {
			// No photos with ratings from this user, return empty paginator
			$this->photos = new LengthAwarePaginator([], 0, $limit);

			return $this->photos;
		}

		// Include all photos with user rating >= cutoff (this handles ties)
		// We need to join with photo_ratings to filter by the user's rating
		$query = $this->photos()
			->join('photo_ratings as pr_filter', function ($join) use ($cutoff_rating): void {
				$join->on('photos.id', '=', 'pr_filter.photo_id')
					->where('pr_filter.user_id', '=', Auth::id() ?? 0)
					->where('pr_filter.rating', '>=', $cutoff_rating);
			})
			->select('photos.*'); // Ensure we only select photo columns

		// Always sort by user's rating DESC for My Best Pictures
		// We already have the join from above, so order by that rating
		$query = $query->orderByRaw('pr_filter.rating DESC')
			->orderBy('photos.created_at', 'DESC');

		/** @var LengthAwarePaginator<int,Photo> $photos */
		$photos = $query->paginate($this->config_manager->getValueAsInt('photos_pagination_limit'));

		$this->photos = $photos;

		return $this->photos;
	}

	/**
	 * Get the rating of the Nth photo (by current user) to use as the cutoff.
	 *
	 * @param int $limit the N for "top N photos"
	 *
	 * @return int|null the rating at position N, or null if fewer than N rated photos exist
	 */
	private function getCutoffRating(int $limit): ?int
	{
		$user_id = Auth::id() ?? 0;

		// Get the Nth highest rating from current user
		$nth_rating = \DB::table('photo_ratings')
			->where('user_id', '=', $user_id)
			->join('photos', 'photo_ratings.photo_id', '=', 'photos.id')
			->orderBy('photo_ratings.rating', 'DESC')
			->skip($limit - 1)
			->take(1)
			->value('photo_ratings.rating');

		if ($nth_rating === null) {
			// Fewer than N photos with ratings from this user exist
			// Get the lowest rating among existing rated photos from this user
			$lowest_rating = \DB::table('photo_ratings')
				->where('user_id', '=', $user_id)
				->join('photos', 'photo_ratings.photo_id', '=', 'photos.id')
				->orderBy('photo_ratings.rating', 'ASC')
				->value('photo_ratings.rating');

			return $lowest_rating;
		}

		return $nth_rating;
	}
}
