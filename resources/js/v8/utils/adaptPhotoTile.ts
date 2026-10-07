import type { PhotoDetailResource, PhotoRatioResource } from "@/services/photo-children-v3-service";
import { formatServerDateTime } from "@/v8/utils/phpDateFormat";

/**
 * `PhotosState.ts.photos` is strictly typed `PhotoResource[]` — every
 * consumer (`PhotoThumb.vue`, `PhotoListItem.vue`, `PhotoState.ts`'s
 * lightbox getters, `contextMenu.ts`) already reads that shape. Rather than
 * widen that type (which would ripple through every existing consumer),
 * `adaptPhotoTile()` synthesizes a genuinely `PhotoResource`-shaped object
 * from one `ratios` row, so those consumers work completely unmodified
 * against SoA-sourced photos too. Fields tier 2 doesn't carry (EXIF,
 * palette, statistics, real size-variant URLs, description, ...) are filled
 * with safe, inert placeholders here and replaced in place by
 * `mergePhotoDetail()` once tier 3 resolves for that specific photo — see
 * `AlbumState.ts`'s `loadPhotoDetails()`.
 *
 * Two fields are *never* filled even after `mergePhotoDetail()` (accepted,
 * documented regressions — NG11/Q-065-06, both because no lightweight field
 * exists in `ratios` and eagerly fetching `details` per tile would defeat
 * the on-demand-only discipline G5 requires):
 * - `face_count` stays `0`, making `PhotoThumb.vue`/`PhotoListItem.vue`'s
 *   hover-triggered face-recognition prefetch a permanent no-op for
 *   SoA-sourced tiles.
 * - `preformatted.filesize` stays `""` until `mergePhotoDetail()` actually
 *   resolves it from `size_variants` (see there) — grid/list tiles never
 *   trigger that fetch, so it stays empty for them, hiding
 *   `PhotoListItem.vue`'s file-size chip exactly as intended.
 */
export type AdaptedPhotoTile = App.Http.Resources.Models.PhotoResource;

const EMPTY_SIZE_VARIANTS: App.Http.Resources.Models.SizeVariantsResouce = {
	raw: null,
	original: null,
	medium2x: null,
	medium: null,
	small2x: null,
	small: null,
	thumb2x: null,
	thumb: null,
	placeholder: null,
};

/**
 * The `Gallery::Init` date formats `PreformattedPhotoData` applies
 * server-side — `useLycheeStateStore()` satisfies this directly.
 */
export type PhotoTileDateFormats = Pick<
	App.Http.Resources.GalleryConfigs.InitConfig,
	"date_format_photo_overlay" | "date_format_sidebar_uploaded" | "date_format_sidebar_taken_at"
>;

/**
 * Everything `adaptPhotoTile()` actually reads off a tier-2 payload.
 *
 * Expressed as `Omit<…, "bucket_ids">` rather than as `PhotoRatioResource` so
 * Feature 069's `SearchPhotoResource` — structurally identical but with no
 * `bucket_ids`, because search has no bucket tier — is accepted too. The
 * function never touched `bucket_ids` in the first place; bucket placement is
 * the caller's concern.
 */
export type AdaptablePhotoTierTwo = Omit<PhotoRatioResource, "bucket_ids">;

/**
 * Adapts row `i` of a `ratios` response into a full, `PhotoResource`-shaped
 * tile. `album_id` is the browsed album's id (the only album a v3-sourced
 * tile is ever linked from in this feature — matching- albums/multi-album
 * membership display is out of scope, NG5). The `preformatted` dates are
 * formatted client-side exactly as `PreformattedPhotoData` does: `taken_at`
 * in the zone the photo was taken in, `created_at` in the viewer's.
 */
export function adaptPhotoTile(i: number, ratios: AdaptablePhotoTierTwo, album_id: string, dateFormats: PhotoTileDateFormats): AdaptedPhotoTile {
	const takenAt = ratios.taken_ats[i];
	const createdAt = ratios.created_ats[i];
	const takenAtTz = ratios.taken_at_orig_tzs[i];
	const formattedTakenAt = formatServerDateTime(takenAt, dateFormats.date_format_sidebar_taken_at, takenAtTz);
	const ratingAvg = ratios.rating_avgs?.[i] ?? null;
	const ratingUser = ratios.rating_users?.[i] ?? null;
	const description = ratios.thumb_infos?.[i] ?? null;
	const tagNames = ratios.tags?.[i] ?? [];

	return {
		id: ratios.ids[i],
		album_id: album_id,
		checksum: "",
		created_at: createdAt,
		description: description ?? "",
		is_highlighted: ratios.is_highlighteds[i],
		license: "none",
		live_photo_checksum: null,
		live_photo_content_id: null,
		live_photo_url: null,
		original_checksum: "",
		size_variants: EMPTY_SIZE_VARIANTS,
		tags: tagNames.map((name, idx) => ({ id: -(idx + 1), name })),
		taken_at: takenAt,
		taken_at_orig_tz: takenAtTz,
		title: ratios.titles[i],
		type: ratios.types[i],
		updated_at: createdAt,
		// Never read from here for the SoA path — next/previous are
		// recomputed client-side from array order (FR-065-15) via the
		// existing `rebuildNavigationLinks()` immediately after this array
		// is set, exactly like the v2 timeline-merge path already does.
		next_photo_id: null,
		previous_photo_id: null,
		preformatted: {
			created_at: formatServerDateTime(createdAt, dateFormats.date_format_sidebar_uploaded),
			taken_at: formattedTakenAt === "" ? null : formattedTakenAt,
			date_overlay:
				takenAt === null
					? formatServerDateTime(createdAt, dateFormats.date_format_photo_overlay)
					: formatServerDateTime(takenAt, dateFormats.date_format_photo_overlay, takenAtTz),
			make: null,
			model: null,
			shutter: "",
			aperture: "",
			iso: "",
			lens: "",
			focal: null,
			duration: "",
			fps: "",
			// Accepted regression (NG11/Q-065-06) — see this file's own doc
			// comment. Backfilled by `mergePhotoDetail()` once `details`
			// resolves for this photo.
			filesize: "",
			resolution: "",
			latitude: null,
			longitude: null,
			altitude: null,
			location: null,
			license: "",
			description: description ?? "",
		},
		precomputed: {
			is_video: ratios.is_videos[i],
			is_raw: ratios.is_raws[i],
			is_livephoto: ratios.is_live_photos[i],
			is_360: ratios.is_360s[i],
			is_camera_date: false,
			has_exif: false,
			has_location: false,
			is_taken_at_modified: false,
			latitude: null,
			longitude: null,
			altitude: null,
		},
		timeline: null,
		palette: null,
		// Feature 082: only full spheres are known from `ratios`; a partial
		// crop arrives with `details` (`mergePhotoDetail()`).
		panorama: null,
		statistics: null,
		rating: ratingAvg === null && ratingUser === null ? null : { rating_avg: ratingAvg ?? 0, rating_user: ratingUser ?? 0, rating_count: 0 },
		// Accepted regression (NG11/Q-065-06) — see this file's own doc
		// comment. Never backfilled, even once `details` resolves: `details`
		// doesn't carry `face_count` for the *bounded* on-demand tier's own
		// sake, so hover-prefetch stays inert on the SoA path by design.
		face_count: 0,
		is_validated: ratios.is_validateds[i],
		owner_id: ratios.owner_ids[i],
	};
}

/**
 * Merges resolved tier-3 fields for one photo, at response index `i`,
 * directly into an already-adapted (or already-full v2) `PhotoResource`
 * object — mutating in place so every existing reactive consumer
 * (`PhotoState.ts`'s getters included) picks up the change with zero
 * getter-level changes. Called by `AlbumState.ts`'s `loadPhotoDetails()`.
 */
export function mergePhotoDetail(photo: AdaptedPhotoTile, detail: PhotoDetailResource, i: number): void {
	const sizeVariants = detail.size_variants[i] ?? EMPTY_SIZE_VARIANTS;

	photo.description = detail.descriptions[i] ?? "";
	photo.tags = detail.tags[i].map((name, idx) => ({ id: -(idx + 1), name }));
	photo.license = (detail.licenses[i] as App.Enum.LicenseType) ?? "none";
	photo.checksum = detail.checksums[i];
	photo.original_checksum = detail.original_checksums[i];
	photo.updated_at = detail.updated_ats[i];
	photo.live_photo_checksum = detail.live_photo_checksums[i];
	photo.live_photo_content_id = detail.live_photo_content_ids[i];
	photo.live_photo_url = detail.live_photo_urls[i];
	photo.palette = detail.palette[i];
	photo.panorama = detail.panoramas[i];
	photo.statistics = detail.statistics[i];
	photo.size_variants = sizeVariants;

	const ratingCount = detail.statistics[i]?.rating_count ?? 0;
	if (photo.rating === null && detail.rating_avgs[i] !== null) {
		photo.rating = { rating_avg: detail.rating_avgs[i]!, rating_user: 0, rating_count: ratingCount };
	} else if (photo.rating !== null && detail.rating_avgs[i] !== null) {
		photo.rating.rating_avg = detail.rating_avgs[i]!;
		photo.rating.rating_count = ratingCount;
	}

	photo.preformatted.description = detail.preformatted_descriptions[i] ?? "";
	photo.preformatted.make = detail.makes?.[i] ?? null;
	photo.preformatted.model = detail.models?.[i] ?? null;
	photo.preformatted.lens = detail.lenses?.[i] ?? "";
	photo.preformatted.aperture = detail.apertures?.[i] ?? "";
	photo.preformatted.shutter = detail.shutters?.[i] ?? "";
	photo.preformatted.focal = detail.focals?.[i] ?? null;
	photo.preformatted.iso = detail.isos?.[i] ?? "";
	photo.preformatted.latitude = detail.latitudes?.[i]?.toString() ?? null;
	photo.preformatted.longitude = detail.longitudes?.[i]?.toString() ?? null;
	photo.preformatted.altitude = detail.altitudes?.[i]?.toString() ?? null;
	photo.preformatted.location = detail.locations?.[i] ?? null;
	photo.preformatted.license = detail.licenses[i] ?? "";
	// The one field `ratios` can never provide but `details`' nested
	// `size_variants` can (each variant carries its own `filesize`) —
	// resolved here rather than accepted as a permanent regression, since
	// `ratios`+`details` together are meant to fully reconstruct
	// `PhotoResource` (Feature 064 NG5/Q-064-06).
	photo.preformatted.filesize = sizeVariants.original?.filesize ?? sizeVariants.medium?.filesize ?? sizeVariants.small?.filesize ?? "";
	if (sizeVariants.original !== null) {
		photo.preformatted.resolution = `${sizeVariants.original.width}x${sizeVariants.original.height}`;
	}

	photo.precomputed.latitude = detail.latitudes?.[i] ?? null;
	photo.precomputed.longitude = detail.longitudes?.[i] ?? null;
	photo.precomputed.altitude = detail.altitudes?.[i] ?? null;
	photo.precomputed.has_location = (detail.latitudes?.[i] ?? null) !== null;
	photo.precomputed.has_exif = (detail.makes?.[i] ?? null) !== null || (detail.models?.[i] ?? null) !== null;
}
