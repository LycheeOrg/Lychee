<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Smart album containing all photos rated by the current user.
 * Shows photos ordered by user's rating (highest first), then by creation date (newest first).
 * Only visible to authenticated users.
 */
class MyRatedPicturesAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::MY_RATED_PICTURES->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		// The condition filters for photos that have been rated by current user
		parent::__construct(
			id: SmartAlbumType::MY_RATED_PICTURES,
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
	 * Override to add custom ordering: user's rating DESC, then created_at DESC.
	 *
	 * @return \App\Eloquent\FixedQueryBuilder<Photo>
	 *
	 * @throws InternalLycheeException
	 */
	public function photos(): Builder
	{
		// Get base query from parent (includes security filtering and smart condition)
		$query = parent::photos();

		// Add join with photo_ratings to access the user's rating for ordering
		// Use leftJoin since the whereHas in smart_condition already ensures ratings exist
		return $query
			->leftJoin('photo_ratings', function ($join): void {
				$join->on('photos.id', '=', 'photo_ratings.photo_id')
					->where('photo_ratings.user_id', '=', Auth::id() ?? 0);
			})
			->orderByRaw('photo_ratings.rating DESC')
			->orderBy('photos.created_at', 'DESC')
			->select('photos.*'); // Ensure we only select photo columns
	}
}
