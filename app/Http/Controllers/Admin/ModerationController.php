<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Moderation\ApproveModerationRequest;
use App\Http\Requests\Moderation\GetModerationPhotoRequest;
use App\Http\Requests\Moderation\ListModerationRequest;
use App\Http\Resources\Collections\PaginatedModerationResource;
use App\Http\Resources\Models\PhotoResource;
use App\Models\Photo;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * Controller for the admin moderation panel.
 *
 * Lists photos awaiting validation (is_validated = false) and
 * supports bulk-approval by setting is_validated = true.
 *
 * Both endpoints are restricted to administrators only.
 */
class ModerationController extends Controller
{
	/**
	 * List all photos that have not yet been validated.
	 *
	 * @param ListModerationRequest $request
	 *
	 * @return PaginatedModerationResource
	 */
	public function list(ListModerationRequest $request): PaginatedModerationResource
	{
		$per_page = min((int) $request->query('per_page', 30), 100);

		/** @var \Illuminate\Pagination\LengthAwarePaginator<Photo> $paginated */
		$paginated = Photo::where('is_validated', false)
			->with(['owner', 'albums', 'size_variants'])
			->orderBy('created_at', 'desc')
			->paginate($per_page);

		return new PaginatedModerationResource($paginated);
	}

	/**
	 * Return the full PhotoResource for a single unvalidated photo.
	 *
	 * Bypasses the is_validated filter since only admins can reach this endpoint.
	 *
	 * @param GetModerationPhotoRequest $request
	 *
	 * @return PhotoResource
	 */
	public function photo(GetModerationPhotoRequest $request): PhotoResource
	{
		/** @var Photo $photo */
		$photo = Photo::where('id', $request->photoId())
			->with(['size_variants', 'palette', 'tags', 'statistics', 'rating', 'albums', 'owner', 'faces', 'faces.person'])
			->firstOrFail();

		return new PhotoResource(
			photo: $photo,
			album_id: null,
			// This is admin view, we don't need to downgrade
			should_downgrade_size_variants: false,
		);
	}

	/**
	 * Bulk-approve a set of photos by marking them as validated.
	 *
	 * @param ApproveModerationRequest $request
	 *
	 * @return Response
	 */
	public function approve(ApproveModerationRequest $request): Response
	{
		$ids = $request->photoIds();

		// Process in chunks to avoid potential query size issues (NFR-033-04)
		collect($ids)->chunk(100)->each(function ($chunk): void {
			Photo::whereIn('id', $chunk)->update(['is_validated' => true]);
		});

		return response()->noContent();
	}
}
