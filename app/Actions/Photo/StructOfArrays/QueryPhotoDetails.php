<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Contracts\Models\AbstractAlbum;
use App\Eloquent\FixedQueryBuilder;
use App\Enum\MetricsAccess;
use App\Http\Resources\Models\ColourPaletteResource;
use App\Http\Resources\Models\PhotoStatisticsResource;
use App\Http\Resources\Models\SizeVariantsResouce;
use App\Http\Resources\V3\PhotoDetailResource;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\PhotoBucketComputer;
use App\SmartAlbums\TimelineAlbum;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

/**
 * Query logic for `GET /api/v3/Albums/{album_id}/Photos/details`.
 *
 * Unlike {@see QueryPhotoBuckets}/{@see QueryPhotoRatios}, this tier does
 * use Eloquent hydration (with `size_variants`/`palette`/`statistics`/`tags`
 * eager-loaded) — a deliberate, scoped exception to the
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
	use ResolvesPhotoSource;

	public function __construct(
		private readonly ConfigManager $config_manager,
		private readonly PhotoBucketComputer $bucket_computer,
		private readonly ResolvesPhotoGrants $resolve_photo_grants,
	) {
	}

	/**
	 * @param string[]|null $photo_ids
	 */
	public function do(AbstractAlbum $album, ?User $user, ?string $bucket_id, ?array $photo_ids): PhotoDetailResource
	{
		$query = $this->resolvePhotoQuery($album, $user)->select('photos.*');

		if ($bucket_id !== null) {
			if ($album instanceof Album) {
				// The literal "unknown" sentinel maps to a NULL bucket_id -
				// uncapped, resolves every matching row.
				if ($bucket_id === 'unknown') {
					$query->whereNull('photo_album.bucket_id');
				} else {
					$query->where('photo_album.bucket_id', '=', $bucket_id);
				}
			} elseif ($album instanceof TimelineAlbum) {
				// SQL-pushdown bounded (NFR-066-01) - never the full-library
				// PHP row scan self::resolveLiveBucketPhotoIds() uses for
				// TagAlbum/PersonAlbum (left untouched, see below).
				$this->applyTimelineBucketFilter($query, $album, $bucket_id);
			} else {
				// TagAlbum/PersonAlbum/BaseSmartAlbum have no stored
				// `bucket_id` to filter by in SQL - resolve the matching ids
				// live first (mirrors QueryPhotoBuckets/QueryPhotoRatios),
				// then curate the main query down to exactly those rows.
				$query->whereIn('photos.id', $this->resolveLiveBucketPhotoIds($album, $user, $bucket_id));
			}
		} else {
			// Ids not actually in this album or not visible to the caller
			// are silently curated away, never a 4xx.
			$query->whereIn('photos.id', $photo_ids ?? []);
		}

		/** @var Collection<int,Photo> $photos */
		$photos = $query->with(['size_variants', 'palette', 'statistics', 'tags'])->get();

		return $this->buildResource($photos, $user);
	}

	/**
	 * Entry point for a photo source that is not an album at all — Feature
	 * 069's search tiers, whose candidate set comes from
	 * {@see \App\Actions\Search\StructOfArrays\SearchPhotoSource} rather than
	 * from {@see ResolvesPhotoSource}.
	 *
	 * Strictly additive: {@see self::do()} is untouched, and both entry points
	 * converge on the same {@see self::buildResource()} projection, so the two
	 * tiers can never drift apart in shape. There is no `bucket_id` branch here
	 * because search has no bucket tier (spec.md NG1) — the caller always scopes
	 * by an explicit, already-capped `photo_ids[]`.
	 *
	 * @param FixedQueryBuilder<Photo> $query     a query already filtered to the caller's visible candidate set
	 * @param string[]                 $photo_ids ids not in the candidate set are silently curated away, never a 4xx
	 */
	public function fromQuery(FixedQueryBuilder $query, ?User $user, array $photo_ids): PhotoDetailResource
	{
		/** @var Collection<int,Photo> $photos */
		$photos = $query
			->select('photos.*')
			->whereIn('photos.id', $photo_ids)
			->with(['size_variants', 'palette', 'statistics', 'tags'])
			->get();

		return $this->buildResource($photos, $user);
	}

	/**
	 * Lean pass over this album's candidate photos to resolve exactly which
	 * ids live-compute to `$bucket_id` (or to `NULL`/"unknown" if
	 * `$bucket_id === 'unknown'`) - see {@see ResolvesPhotoSource} for why a
	 * non-`Album` source has no stored `bucket_id` column to filter by
	 * directly in SQL.
	 *
	 * @return string[]
	 */
	private function resolveLiveBucketPhotoIds(AbstractAlbum $album, ?User $user, string $bucket_id): array
	{
		$sorting = $this->resolveEffectiveSorting($album);
		$granularity = $this->bucket_computer->resolveGranularity($this->resolvePhotoTimeline($album));

		$query = $this->resolvePhotoQuery($album, $user);
		$rows = $query->select([
			'photos.id', 'photos.title', 'photos.title_base', 'photos.created_at', 'photos.taken_at',
			'photos.is_highlighted', 'photos.type', 'photos.rating_avg',
		])->toBase()->get();

		$matching_ids = [];
		foreach ($rows as $row) {
			$row_bucket_id = $this->liveBucketId($sorting->column, $granularity, $row) ?? 'unknown';
			if ($row_bucket_id === $bucket_id) {
				$matching_ids[] = $row->id;
			}
		}

		return $matching_ids;
	}

	/**
	 * Curates `$query` (already scoped to {@see TimelineAlbum}'s candidate
	 * photos by {@see ResolvesPhotoSource::resolvePhotoQuery()}) down to
	 * exactly `$bucket_id`'s rows via a direct `WHERE` range on the raw
	 * sort column - `PhotoBucketComputer::bucketDateRange()`'s SQL-pushdown
	 * `[start, end)`, or `whereNull()` for the `"unknown"` sentinel - bounded
	 * by the requested bucket's own size, never the full library
	 * (NFR-066-01, FR-066-07).
	 *
	 * @param FixedQueryBuilder<Photo> $query
	 */
	private function applyTimelineBucketFilter(FixedQueryBuilder $query, TimelineAlbum $album, string $bucket_id): void
	{
		$sorting = $this->resolveEffectiveSorting($album);

		if ($bucket_id === 'unknown') {
			$query->whereNull($sorting->column->value);

			return;
		}

		$granularity = $this->bucket_computer->resolveGranularity($this->resolvePhotoTimeline($album));
		[$start, $end] = $this->bucket_computer->bucketDateRange($bucket_id, $granularity);

		$query
			->where($sorting->column->value, '>=', $start)
			->where($sorting->column->value, '<', $end);
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

		// One grouped query for the whole page, replacing a per-photo policy
		// evaluation that lazy-loaded each containing album's
		// `access_permissions` (6 queries for 4 photos, measured).
		$should_downgrade_by_id = $this->resolve_photo_grants->downgradeMap($photos, $user);

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
			// Batched equivalent of `PhotoPolicy::canAccessFullPhoto()`, resolved
			// for the whole page in one query by `ResolvesPhotoGrants`.
			// `canSee()` needs no check here - every row reaching this point
			// survived the tier's own visibility-filtered candidate query.
			$size_variants[] = new SizeVariantsResouce($photo, $should_downgrade_by_id[$photo->id] ?? true);

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
