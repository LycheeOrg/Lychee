<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Constants\PhotoAlbum;
use App\Enum\NsfwSensitiveAlbumAction;
use App\Enum\NsfwStatus;
use App\Http\Requests\Moderation\ApproveModerationRequest;
use App\Http\Requests\Moderation\GetModerationPhotoRequest;
use App\Http\Requests\Moderation\ListModerationRequest;
use App\Http\Resources\Collections\PaginatedModerationResource;
use App\Http\Resources\Models\PhotoResource;
use App\Jobs\ApplyNsfwAlbumSensitivityJob;
use App\Models\NsfwDetection;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
			->with(['size_variants', 'palette', 'tags', 'statistics', 'rating', 'albums.access_permissions', 'owner', 'faces', 'faces.person'])
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
	public function approve(ApproveModerationRequest $request, ConfigManager $config_manager): Response
	{
		$ids = $request->photoIds();

		collect($ids)->chunk(100)->each(fn ($chunk) => $this->approvePhotos($chunk, $config_manager));

		return response()->noContent();
	}

	/**
	 * Here we do the approval on chunks.
	 * We use early returns to simplify the logic and avoid deep nesting.
	 *
	 * Uses a hybrid two-pass approach:
	 * Pass 1 (bulk): set is_validated = true for all photos.
	 * Pass 2 (NSFW subset): update nsfw_status and dispatch album sensitivity jobs.
	 *
	 * @param Collection<string> $photo_ids
	 * @param ConfigManager      $config_manager
	 *
	 * @return void
	 */
	private function approvePhotos(Collection $photo_ids, ConfigManager $config_manager): void
	{
		// Pass 1: Bulk approve
		Photo::whereIn('id', $photo_ids)->update(['is_validated' => true]);

		// Pass 2: NSFW subset — update nsfw_status and dispatch album jobs
		$nsfw_photos = Photo::whereIn('id', $photo_ids)
			->where('nsfw_status', NsfwStatus::REVIEW)
			->select('id')
			->pluck('id');

		if ($nsfw_photos->isEmpty()) {
			return;
		}

		Photo::whereIn('id', $nsfw_photos)->update(['nsfw_status' => NsfwStatus::VISIBLE->value]);

		if ($config_manager->getValueAsEnum(
			'ai_vision_nsfw_sensitive_album_action',
			NsfwSensitiveAlbumAction::class)
				!== NsfwSensitiveAlbumAction::MARK_ALBUM) {
			return;
		}

		$sensitive_photo_ids = NsfwDetection::whereIn('photo_id', $nsfw_photos)
			->where('is_sensitive', true)
			->distinct()
			->pluck('photo_id');

		if ($sensitive_photo_ids->isNotEmpty()) {
			$all_album_ids = DB::table(PhotoAlbum::PHOTO_ALBUM)
				->whereIn(PhotoAlbum::PHOTO_ID, $sensitive_photo_ids)
				->select(PhotoAlbum::ALBUM_ID)
				->distinct()
				->pluck('album_id')->all();

			if ($all_album_ids !== []) {
				ApplyNsfwAlbumSensitivityJob::dispatch($all_album_ids);
			}
		}
	}
}
