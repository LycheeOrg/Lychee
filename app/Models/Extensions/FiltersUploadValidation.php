<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Extensions;

/**
 * Provides the upload-validation visibility filter for photo queries.
 *
 * Photos with `is_upload_validated = false` are only visible to their owner.
 * Admin callers must apply an early return before calling this helper.
 */
trait FiltersUploadValidation
{
	/**
	 * Adds the upload-validation visibility filter to a photo query.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder<\App\Models\Photo>|\App\Eloquent\FixedQueryBuilder<\App\Models\Photo> $query   the query builder
	 * @param int|null                                                                                                    $user_id the authenticated user's ID, or null for guests
	 */
	private function applyUploadValidationFilter($query, ?int $user_id): void
	{
		$query->where(function ($q) use ($user_id): void {
			$q->where('photos.is_upload_validated', '=', true);
			if ($user_id !== null) {
				$q->orWhere('photos.owner_id', '=', $user_id);
			}
		});
	}
}
