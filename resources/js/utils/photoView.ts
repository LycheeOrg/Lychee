/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

type Photo = App.Http.Resources.Models.PhotoResource;

export enum ImageViewMode {
	Original = "original",
	Medium = "medium",
	Raw = "raw",
	Video = "video",
	LivePhotoMedium = "livephoto-medium",
	LivePhotoOriginal = "livephoto-original",
	Pdf = "pdf",
	/** Feature 082: chosen by the v8 `PhotoBox` only, never returned by `imageViewModeOf()`. */
	Sphere = "sphere",
}

/** The photos the photo view requests details for: 10 ahead and 5 behind the shown one (FR-103-08). */
const DETAILS_AHEAD = 10;
const DETAILS_BEHIND = 5;

/**
 * A struct-of-arrays tile whose details are not merged yet: `adaptPhotoTile()` leaves every size
 * variant empty, while a real photo always has its original and thumb (FR-103-07).
 */
export function isDetailPending(photo: Photo): boolean {
	return photo.size_variants.original === null && photo.size_variants.thumb === null;
}

/** What the photo view shows for `photo`, as the `PhotoState.imageViewMode` getter does for the shown photo. */
export function imageViewModeOf(photo: Photo, prefersHighestQuality: boolean): ImageViewMode {
	if (photo.precomputed.is_video) {
		return ImageViewMode.Video;
	}

	if (photo.precomputed.is_raw) {
		if (photo.size_variants.medium !== null) {
			return ImageViewMode.Medium;
		}
		if (photo.size_variants.original?.url?.endsWith(".pdf")) {
			return ImageViewMode.Pdf;
		}
		return ImageViewMode.Raw;
	}

	// When enabled, prefer the highest-quality variant that exists for
	// this photo (Original, falling back to Medium) instead of always
	// preferring Medium - e.g. so a mobile long-press-save captures
	// Original rather than whatever the viewer would otherwise render.
	const hasMedium = photo.size_variants.medium !== null;
	const hasOriginal = !!photo.size_variants.original?.url;

	if (photo.precomputed.is_livephoto === true) {
		if (prefersHighestQuality) {
			return hasOriginal ? ImageViewMode.LivePhotoOriginal : ImageViewMode.LivePhotoMedium;
		}
		return hasMedium ? ImageViewMode.LivePhotoMedium : ImageViewMode.LivePhotoOriginal;
	}

	if (prefersHighestQuality) {
		return hasOriginal ? ImageViewMode.Original : ImageViewMode.Medium;
	}
	return hasMedium ? ImageViewMode.Medium : ImageViewMode.Original;
}

export function srcSetMediumOf(photo: Photo): string {
	const medium = photo.size_variants.medium ?? null;
	const medium2x = photo.size_variants.medium2x ?? null;
	if (medium === null || medium2x === null) {
		return "";
	}

	return `${medium.url} ${medium.width}w, ${medium2x.url} ${medium2x.width}w`;
}

export function sizesMediumOf(photo: Photo): string {
	const medium = photo.size_variants.medium ?? null;
	if (srcSetMediumOf(photo) === "" || medium === null || medium.height === 0) {
		return "";
	}

	// The image is fitted into the viewport, so its rendered width is capped by
	// the viewport height times its aspect ratio. Ignoring the surrounding chrome
	// only overestimates, i.e. errs towards medium2x, never towards a blurry medium.
	return `min(100vw, ${(medium.width / medium.height).toFixed(4)} * 100vh)`;
}

/**
 * Ids whose details the photo view requests when showing `photoId`: the photo itself, the
 * 10 next and the 5 previous in grid order (FR-103-08). Empty when `photoId` is not in `photos`.
 */
export function detailWindowIds(photos: Photo[], photoId: string): string[] {
	const index = photos.findIndex((p) => p.id === photoId);
	if (index === -1) {
		return [];
	}
	const ahead = photos.slice(index + 1, index + 1 + DETAILS_AHEAD).map((p) => p.id);
	const behind = photos
		.slice(Math.max(0, index - DETAILS_BEHIND), index)
		.map((p) => p.id)
		.reverse();
	return [photoId, ...ahead, ...behind];
}

export type PreloadSource = { src: string; srcset: string; sizes: string };

/**
 * The image the photo view would load for `photo`, as `PhotoBox` renders it: the medium
 * `srcset`/`sizes` or the original. Null for videos, PDFs, raw placeholders and pending details,
 * which are not preloaded (FR-103-08).
 */
export function preloadSourceOf(photo: Photo, prefersHighestQuality: boolean): PreloadSource | null {
	if (isDetailPending(photo)) {
		return null;
	}

	switch (imageViewModeOf(photo, prefersHighestQuality)) {
		case ImageViewMode.Medium:
		case ImageViewMode.LivePhotoMedium: {
			const url = photo.size_variants.medium?.url ?? null;
			return url === null ? null : { src: url, srcset: srcSetMediumOf(photo), sizes: sizesMediumOf(photo) };
		}
		case ImageViewMode.Original:
		case ImageViewMode.LivePhotoOriginal: {
			const url = photo.size_variants.original?.url ?? null;
			return url === null ? null : { src: url, srcset: "", sizes: "" };
		}
		default:
			return null;
	}
}
