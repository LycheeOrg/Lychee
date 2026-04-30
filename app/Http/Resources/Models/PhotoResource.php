<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\LicenseType;
use App\Http\Resources\Models\Utils\PreComputedPhotoData;
use App\Http\Resources\Models\Utils\PreformattedPhotoData;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Models\Face;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoResource extends Data
{
	public string $id;
	public ?string $album_id;
	public string $checksum;
	public string $created_at;
	public string $description;
	public bool $is_highlighted;
	public LicenseType $license;
	public ?string $live_photo_checksum;
	public ?string $live_photo_content_id;
	public ?string $live_photo_url;
	public string $original_checksum;
	public SizeVariantsResouce $size_variants;
	/** @var string[] */
	public array $tags;
	public ?string $taken_at;
	public ?string $taken_at_orig_tz;
	public string $title;
	public string $type;
	public string $updated_at;
	public ?string $next_photo_id;
	public ?string $previous_photo_id;
	public PreformattedPhotoData $preformatted;
	public PreComputedPhotoData $precomputed;
	public ?TimelineData $timeline = null;
	public ?ColourPaletteResource $palette = null;

	private Carbon $timeline_data_carbon;

	public ?PhotoStatisticsResource $statistics = null;
	public ?PhotoRatingResource $rating = null;
	/** @var FaceResource[] */
	public array $faces = [];
	public int $hidden_face_count = 0;
	public bool $is_validated;

	public function __construct(Photo $photo, ?string $album_id, bool $should_downgrade_size_variants)
	{
		$this->id = $photo->id;
		$this->album_id = $album_id;
		$this->checksum = $photo->checksum;
		$this->created_at = $photo->created_at->toIso8601String();
		$this->description = $photo->description ?? '';
		$this->is_highlighted = $photo->is_highlighted;
		$this->is_validated = $photo->is_validated;
		$this->license = $photo->license;
		$this->live_photo_checksum = $photo->live_photo_checksum;
		$this->live_photo_content_id = $photo->live_photo_content_id;
		$this->live_photo_url = $photo->live_photo_url;
		$this->original_checksum = $photo->original_checksum;
		$this->size_variants = new SizeVariantsResouce($photo, $should_downgrade_size_variants);
		$this->tags = $photo->tags->pluck('name')->all();
		$this->taken_at = $photo->taken_at?->toIso8601String();
		$this->taken_at_orig_tz = $photo->taken_at_orig_tz;
		$this->title = (request()->configs()->getValueAsBool('file_name_hidden') && Auth::guest()) ? '' : $photo->title;
		$this->type = $photo->type;
		$this->updated_at = $photo->updated_at->toIso8601String();
		$this->next_photo_id = null;
		$this->previous_photo_id = null;
		$include_exif_data = request()->configs()->getValueAsBool('display_exif_data');
		$this->preformatted = new PreformattedPhotoData($photo, $include_exif_data, $this->size_variants->original);
		$this->precomputed = new PreComputedPhotoData($photo, $include_exif_data);
		$this->palette = ColourPaletteResource::fromModel($photo->palette);

		$this->timeline_data_carbon = $photo->taken_at ?? $photo->created_at;

		if (request()->configs()->getValueAsBool('metrics_enabled') && Gate::check(PhotoPolicy::CAN_READ_METRICS, [Photo::class, $photo])) {
			$this->statistics = PhotoStatisticsResource::fromModel($photo->statistics);
		}

		if (request()->configs()->getValueAsBool('rating_enabled') &&
			Gate::check(PhotoPolicy::CAN_READ_RATINGS, [Photo::class, $photo])) {
			$this->rating = PhotoRatingResource::fromModel(
				$photo->statistics,
				$photo->rating,
				request()->configs(),
			);
		}

		$this->buildFaceData($photo);
	}

	/**
	 * Populate faces[] and hidden_face_count from the photo's detected faces.
	 *
	 * Only runs when ai_vision_face_enabled is on and the viewer has permission
	 * to see face overlays per the PhotoPolicy::CAN_VIEW_FACE_OVERLAYS gate.
	 * Non-searchable persons are hidden from viewers who are not the linked user;
	 * they are counted in hidden_face_count instead.
	 */
	private function buildFaceData(Photo $photo): void
	{
		if (!Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $photo)) {
			return;
		}

		$user = Auth::user();
		$is_admin = $user?->may_administrate === true;

		foreach ($photo->faces as $face) {
			/** @var Face $face */
			if ($face->is_dismissed) {
				continue;
			}

			// Unassigned face or searchable person: always include.
			if ($face->person_id === null || ($face->person !== null && $face->person->is_searchable)) {
				$this->faces[] = new FaceResource($face);
				continue;
			}

			// Non-searchable person: visible only to the linked user or admin.
			if ($is_admin || ($user !== null && $face->person?->user_id === $user->id)) {
				$this->faces[] = new FaceResource($face);
			} else {
				$this->hidden_face_count++;
			}
		}
	}

	/**
	 * Accessors to the Carbon instances.
	 *
	 * @return Carbon
	 */
	public function timeline_date_carbon(): Carbon
	{
		return $this->timeline_data_carbon;
	}
}
