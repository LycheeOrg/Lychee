<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Exceptions\ConflictingPropertyException;
use App\Models\Photo;
use App\Models\PhotoRating;
use App\Models\Statistics;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Rating
{
	/**
	 * Set or update the rating for the photo.
	 *
	 * Handles:
	 * - Creating new ratings (rating > 0, no existing rating)
	 * - Updating existing ratings (rating > 0, existing rating)
	 * - Removing ratings (rating == 0)
	 * - Atomic statistics updates
	 *
	 * @param Photo $photo  The photo to rate
	 * @param User  $user   The user rating the photo
	 * @param int   $rating The rating value (0-5, where 0 removes the rating)
	 *
	 * @return Photo the photo with refreshed statistics
	 *
	 * @throws ConflictingPropertyException if a database conflict occurs during the transaction
	 */
	public function do(Photo $photo, User $user, int $rating): Photo
	{
		try {
			DB::transaction(function () use ($photo, $user, $rating): void {
				// Ensure statistics record exists atomically (Q001-07)
				$statistics = Statistics::firstOrCreate(
					['photo_id' => $photo->id],
					[
						'album_id' => null,
						'visit_count' => 0,
						'download_count' => 0,
						'favourite_count' => 0,
						'shared_count' => 0,
						'rating_sum' => 0,
						'rating_count' => 0,
					]
				);

				if ($rating > 0) {
					// Find existing rating by this user for this photo
					$existing_rating = PhotoRating::where('photo_id', $photo->id)
						->where('user_id', $user->id)
						->first();

					if ($existing_rating !== null) {
						// Update: adjust statistics delta
						$delta = $rating - $existing_rating->rating;
						$statistics->rating_sum += $delta;
						$existing_rating->rating = $rating;
						$existing_rating->save();
					} else {
						// Insert: create new rating and increment statistics
						PhotoRating::create([
							'photo_id' => $photo->id,
							'user_id' => $user->id,
							'rating' => $rating,
						]);
						$statistics->rating_sum += $rating;
						$statistics->rating_count++;
					}

					$statistics->save();
				} else {
					// Rating == 0: remove rating (idempotent, Q001-06)
					$existing_rating = PhotoRating::where('photo_id', $photo->id)
						->where('user_id', $user->id)
						->first();

					if ($existing_rating !== null) {
						$statistics->rating_sum -= $existing_rating->rating;
						$statistics->rating_count--;
						$statistics->save();
						$existing_rating->delete();
					}
					// If no existing rating, do nothing (idempotent)
				}
			});

			// Reload photo with fresh statistics
			$photo->refresh();

			return $photo;
		} catch (\Throwable $e) {
			throw new ConflictingPropertyException('Failed to update photo rating due to a conflict. Please try again.', $e);
		}
	}
}
