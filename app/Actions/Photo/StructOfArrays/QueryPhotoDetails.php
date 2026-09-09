<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Constants\PhotoAlbum as PA;
use App\Enum\MetricsAccess;
use App\Http\Resources\Models\ColourPaletteResource;
use App\Http\Resources\Models\PhotoStatisticsResource;
use App\Http\Resources\Models\SizeVariantsResouce;
use App\Http\Resources\V3\PhotoDetailResource;
use App\Models\Album;
use App\Models\Extensions\FiltersUploadValidation;
use App\Models\Photo;
use App\Models\User;
use App\Policies\PhotoPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Optional;

/**
 * Query logic for `GET /api/v3/Albums/{album_id}/Photos/details`.
 *
 * Unlike {@see QueryPhotoBuckets}/{@see QueryPhotoRatios}, this tier does
 * use Eloquent hydration (with `size_variants`/`palette`/`statistics`/
 * `tags`/`albums` eager-loaded) — a deliberate, scoped exception to the
 * other two tiers' `toBase()`-only discipline: reusing
 * `SizeVariantsResouce`/`ColourPaletteResource`/`PhotoStatisticsResource`
 * exactly as-is requires those classes' hydrated-`Photo`-model constructor,
 * not raw scalars. This is safe at this tier's scale specifically: `details`
 * is never whole-album (`photo_ids[]` is capped at 300 as input; `bucket_id`
 * mode is uncapped but bounded implicitly by that bucket's own size, which
 * the client already knows from tier 1) — the scale concern that rules out
 * Eloquent hydration for the whole-album `buckets`/`ratios` tiers doesn't
 * apply here.
 */
class QueryPhotoDetails
{
	use FiltersUploadValidation;

	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * @param string[]|null $photo_ids
	 */
	public function do(Album $album, ?User $user, ?string $bucket_id, ?array $photo_ids): PhotoDetailResource
	{
		$query = Photo::query()
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, '=', $album->id)
			->select('photos.*');

		// Non-admins must not see unvalidated photos uploaded by other
		// users.
		if ($user?->may_administrate !== true) {
			$this->applyUploadValidationFilter($query, $user?->id);
		}

		if ($bucket_id !== null) {
			// The literal "unknown" sentinel maps to a NULL bucket_id -
			// uncapped, resolves every matching row.
			if ($bucket_id === 'unknown') {
				$query->whereNull('photo_album.bucket_id');
			} else {
				$query->where('photo_album.bucket_id', '=', $bucket_id);
			}
		} else {
			// Ids not actually in this album or not visible to the caller
			// are silently curated away, never a 4xx.
			$query->whereIn('photos.id', $photo_ids ?? []);
		}

		/** @var Collection<int,Photo> $photos */
		$photos = $query->with(['size_variants', 'palette', 'statistics', 'tags', 'albums'])->get();

		return $this->buildResource($photos, $user);
	}

	/**
	 * @param Collection<int,Photo> $photos
	 */
	private function buildResource(Collection $photos, ?User $user): PhotoDetailResource
	{
		$include_exif = $this->config_manager->getValueAsBool('display_exif_data');
		$gps_on = $this->config_manager->getValueAsBool('gps_coordinate_display') &&
			($user !== null || $this->config_manager->getValueAsBool('gps_coordinate_display_public'));
		$location_on = $this->config_manager->getValueAsBool('location_show') &&
			($user !== null || $this->config_manager->getValueAsBool('location_show_public'));
		$metrics_enabled = $this->config_manager->getValueAsBool('metrics_enabled');
		$metrics_access = $this->config_manager->getValueAsEnum('metrics_access', MetricsAccess::class);

		$ids = [];
		$descriptions = [];
		$tags = [];
		$rating_avgs = [];
		$licenses = [];
		$owner_ids = [];
		$nsfw_statuses = [];
		$checksums = [];
		$original_checksums = [];
		$updated_ats = [];
		$live_photo_checksums = [];
		$live_photo_content_ids = [];
		$live_photo_urls = [];
		$face_counts = [];
		$palette = [];
		$size_variants = [];
		$statistics = [];
		$makes = [];
		$models = [];
		$lenses = [];
		$apertures = [];
		$shutters = [];
		$focals = [];
		$isos = [];
		$latitudes = [];
		$longitudes = [];
		$altitudes = [];
		$locations = [];

		foreach ($photos as $photo) {
			$ids[] = $photo->id;
			$descriptions[] = $photo->description;
			$tags[] = $photo->tags->pluck('name')->all();
			$rating_avgs[] = $photo->rating_avg !== null ? (float) $photo->rating_avg : null;
			$licenses[] = $photo->license->value;
			$owner_ids[] = $photo->owner_id;
			$nsfw_statuses[] = $photo->nsfw_status?->value;
			$checksums[] = $photo->checksum;
			$original_checksums[] = $photo->original_checksum;
			$updated_ats[] = $photo->updated_at->toIso8601String();
			$live_photo_checksums[] = $photo->live_photo_checksum;
			$live_photo_content_ids[] = $photo->live_photo_content_id;
			$live_photo_urls[] = $photo->live_photo_url;
			$face_counts[] = $photo->face_count;

			$palette[] = ColourPaletteResource::fromModel($photo->palette);
			$should_downgrade = !Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]);
			$size_variants[] = new SizeVariantsResouce($photo, $should_downgrade);

			// The one genuinely per-row (not per-request) gate here -
			// `metrics_access=owner` depends on *this row's* owner_id, not
			// a request-wide constant.
			$can_read_metrics = $metrics_enabled && match ($metrics_access) {
				MetricsAccess::PUBLIC => true,
				MetricsAccess::LOGGED_IN => $user !== null,
				MetricsAccess::OWNER => $user !== null && $photo->owner_id === $user->id,
				MetricsAccess::ADMIN => $user?->may_administrate === true,
				default => false,
			};
			$statistics[] = $can_read_metrics ? PhotoStatisticsResource::fromModel($photo->statistics) : null;

			if ($include_exif) {
				$makes[] = $photo->make;
				$models[] = $photo->model;
				$lenses[] = $photo->lens;
				$apertures[] = $photo->aperture;
				$shutters[] = $photo->shutter;
				$focals[] = $photo->focal;
				$isos[] = $photo->iso;
			}

			if ($gps_on) {
				$latitudes[] = $photo->latitude;
				$longitudes[] = $photo->longitude;
				$altitudes[] = $photo->altitude;
			}

			if ($location_on) {
				$locations[] = $photo->location;
			}
		}

		return new PhotoDetailResource(
			ids: $ids,
			descriptions: $descriptions,
			tags: $tags,
			rating_avgs: $rating_avgs,
			licenses: $licenses,
			owner_ids: $owner_ids,
			nsfw_statuses: $nsfw_statuses,
			checksums: $checksums,
			original_checksums: $original_checksums,
			updated_ats: $updated_ats,
			live_photo_checksums: $live_photo_checksums,
			live_photo_content_ids: $live_photo_content_ids,
			live_photo_urls: $live_photo_urls,
			face_counts: $face_counts,
			palette: $palette,
			size_variants: $size_variants,
			statistics: $statistics,
			makes: $include_exif ? $makes : Optional::create(),
			models: $include_exif ? $models : Optional::create(),
			lenses: $include_exif ? $lenses : Optional::create(),
			apertures: $include_exif ? $apertures : Optional::create(),
			shutters: $include_exif ? $shutters : Optional::create(),
			focals: $include_exif ? $focals : Optional::create(),
			isos: $include_exif ? $isos : Optional::create(),
			latitudes: $gps_on ? $latitudes : Optional::create(),
			longitudes: $gps_on ? $longitudes : Optional::create(),
			altitudes: $gps_on ? $altitudes : Optional::create(),
			locations: $location_on ? $locations : Optional::create(),
		);
	}
}
