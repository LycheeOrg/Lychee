import { defineStore } from "pinia";
import { usePhotosStore } from "./PhotosState";
import { useAlbumStore } from "./AlbumState";
import { useTimelineStore } from "./TimelineState";
import { useLycheeStateStore } from "./LycheeState";
import { useSearchStore } from "./SearchState";
import { isWebGL2Supported } from "@/v8/utils/webgl";
import { detailWindowIds, ImageViewMode, imageViewModeOf, isDetailPending, preloadSourceOf, sizesMediumOf, srcSetMediumOf } from "@/utils/photoView";

export { ImageViewMode };

export type PhotoStore = ReturnType<typeof usePhotoStore>;

/**
 * Zoom functions of the mounted v8 `PhotoBox` (Feature 078, DO-078-05), used by the
 * panel keyboard shortcuts. The zoom state itself stays in `PhotoBox`.
 */
export type ZoomControls = {
	toggle: () => void;
	zoomIn: () => void;
	zoomOut: () => void;
	reset: () => void;
};

/**
 * Darkened thumb background for the previous/next buttons. A neighbour synthesised from a
 * Struct-of-Arrays listing has no size variants until its details are merged: no background
 * then, otherwise `url('undefined')` requests `/undefined`.
 */
function neighbourStyle(photo: App.Http.Resources.Models.PhotoResource): string {
	const thumbUrl = photo.size_variants.thumb?.url;
	if (thumbUrl === undefined || thumbUrl === null) {
		return "";
	}
	return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + thumbUrl + "')";
}

/** Neighbours whose image is preloaded once their details are merged (FR-103-08). */
const PRELOAD_AHEAD = 3;
const PRELOAD_BEHIND = 1;

/** Sources already handed to the browser, so a photo is not preloaded twice. */
const preloaded = new Set<string>();

/**
 * Asks the browser for the image the photo view would show for each neighbour of `index`
 * (FR-103-08). A detached `Image` with the same `srcset`/`sizes` makes the browser pick, fetch and
 * cache exactly what `PhotoBox` will request.
 */
function preloadAround(photos: App.Http.Resources.Models.PhotoResource[], index: number, prefersHighestQuality: boolean): void {
	const neighbours = [...photos.slice(index + 1, index + 1 + PRELOAD_AHEAD), ...photos.slice(Math.max(0, index - PRELOAD_BEHIND), index)];
	for (const photo of neighbours) {
		const source = preloadSourceOf(photo, prefersHighestQuality);
		if (source === null || preloaded.has(source.src)) {
			continue;
		}
		if (preloaded.size > 500) {
			preloaded.clear();
		}
		preloaded.add(source.src);
		const image = new Image();
		if (source.srcset !== "") {
			image.sizes = source.sizes;
			image.srcset = source.srcset;
		}
		image.src = source.src;
	}
}

export const usePhotoStore = defineStore("photo-store", {
	state: () => ({
		photoId: undefined as string | undefined,
		photo: undefined as App.Http.Resources.Models.PhotoResource | undefined,
		transition: "slide-next" as "slide-next" | "slide-previous",
		// Feature 078: written by the v8 `PhotoBox` only (mounted, zoomable photo).
		is_zoomed: false,
		zoom_controls: undefined as ZoomControls | undefined,
		// Feature 082: photo switched to its flat view (FR-082-09), photo whose sphere failed (FR-082-15).
		sphere_flat_photo_id: undefined as string | undefined,
		sphere_failed_photo_id: undefined as string | undefined,
		/** FR-103-07: the shown photo's details request failed; showing it again retries. */
		details_failed_photo_id: undefined as string | undefined,
	}),
	actions: {
		reset() {
			this.photoId = undefined;
			this.photo = undefined;
			this.transition = "slide-next";
			this.is_zoomed = false;
			this.zoom_controls = undefined;
			this.sphere_flat_photo_id = undefined;
			this.sphere_failed_photo_id = undefined;
			this.details_failed_photo_id = undefined;
		},
		/** FR-082-09: between the sphere and the flat image; the next photo opens as a sphere again. */
		toggleSphereFlat() {
			if (this.photo === undefined) {
				return;
			}
			this.sphere_flat_photo_id = this.sphere_flat_photo_id === this.photo.id ? undefined : this.photo.id;
		},
		markSphereFailed(photo_id: string) {
			this.sphere_failed_photo_id = photo_id;
		},
		setTransition(photo_id: string | undefined | null) {
			if (photo_id === undefined || photo_id === null) {
				return;
			}

			if (this.photo !== undefined) {
				this.transition = this.photo.next_photo_id === photo_id ? "slide-next" : "slide-previous";
			} else {
				this.transition = "slide-next";
			}
		},
		load() {
			if (this.photoId === undefined) {
				this.photo = undefined;
				return;
			}

			if (this.photo?.id === this.photoId) {
				// Already loaded
				return;
			}

			const photosState = usePhotosStore();
			if (photosState.photos.length === 0) {
				this.photo = undefined;
				return;
			}
			this.photo = photosState.photos.find((p) => p.id === this.photoId);
			this.details_failed_photo_id = undefined;

			// On-demand tier-3 (`details`) fetch (G5), gated on `isPhotoSoaActive`
			// (Q-065-05, Option A): only fires on the SoA path, where the tile
			// found above is a lightweight, `ratios`-derived object still
			// missing description/EXIF/size-variant-URL/etc. On the v2 path
			// (or a TagAlbum/PersonAlbum/filtered view) `this.photo` is already
			// a full `PhotoResource` — calling `loadPhotoDetails()` there would
			// either 404 (Feature 064's endpoints reject non-Album parents) or
			// be a wasted round trip against already-complete data, so it is
			// never called at all in that case. The request also carries the
			// 10 next and 5 previous photos in grid order, and the images of
			// the nearest ones are preloaded once merged, so fast navigation
			// finds them ready (FR-103-08).
			//
			// Feature 066: a Timeline-sourced tile is exactly as
			// `ratios`-lightweight (`adaptPhotoTile()` is reused unmodified,
			// FR-066-11) and needs the same on-demand tier-3 resolution — routed
			// to `timelineStore.loadPhotoDetailsV3()` instead, gated on
			// `photo.album_id === "timeline"` (the literal id every
			// Timeline-adapted tile carries) rather than on the current route,
			// so this stays correct even mid-transition between views. The two
			// gates are mutually exclusive in practice (a v3 album tile's
			// `album_id` is never `"timeline"`), but checked as a clean
			// `else if` regardless — never both in the same call.
			//
			// Feature 069: a search-sourced tile is `ratios`-lightweight in the
			// same way and needs the same resolution, but it cannot be
			// recognised by its `album_id` the way a Timeline tile can — a
			// search tile carries a *real* album id (FR-069-04), indistinguishable
			// from an album-browsing tile's. Nor is membership in the search
			// store's own tile list enough — that list outlives the search route
			// (only an explicit clear empties it), so a photo that is in both a
			// search result and the album being browsed would match it. The tile
			// is identified by who last wrote `photosStore.photos` instead.
			//
			// That branch is checked **first**, and the ordering is load-bearing:
			// an album-scoped search leaves `albumStore` holding that origin
			// album, so `isPhotoSoaActive` can be true on the search route too.
			// Falling through to it would fetch details from
			// `/Albums/{origin}/Photos/details`, which is wrong for any result
			// outside that album and silently returns nothing for it.
			if (this.photo !== undefined) {
				const albumStore = useAlbumStore();
				const timelineStore = useTimelineStore();
				const searchStore = useSearchStore();
				const ids = detailWindowIds(photosState.photos, this.photo.id);
				let request: Promise<boolean> | undefined;
				if (searchStore.isSearchSoaActive && photosState.sourceV3 === "search") {
					request = searchStore.loadPhotoDetailsV3(ids);
				} else if (albumStore.isPhotoSoaActive) {
					request = albumStore.loadPhotoDetails(ids);
				} else if (timelineStore.isTimelineSoaActive && this.photo.album_id === "timeline") {
					request = timelineStore.loadPhotoDetailsV3(ids);
				}
				void request?.then((succeeded) => this.afterDetails(ids[0], succeeded));
			}
		},
		/**
		 * FR-103-07/08: a failed request leaves the shown photo without details, so the view shows
		 * its error state; otherwise the nearest neighbours' images are preloaded.
		 */
		afterDetails(photoId: string, succeeded: boolean) {
			if (this.photo?.id !== photoId) {
				return;
			}
			if (!succeeded && isDetailPending(this.photo)) {
				this.details_failed_photo_id = photoId;
				return;
			}
			const photosState = usePhotosStore();
			const index = photosState.photos.findIndex((p) => p.id === photoId);
			preloadAround(photosState.photos, index, useLycheeStateStore().is_photo_viewer_highest_quality_enabled);
		},
	},
	getters: {
		isLoaded(): boolean {
			return this.photo !== undefined;
		},
		hasPrevious(): boolean {
			return this.photo?.previous_photo_id !== null && this.photo?.previous_photo_id !== undefined;
		},
		hasNext(): boolean {
			return this.photo?.next_photo_id !== null && this.photo?.next_photo_id !== undefined;
		},
		// For displaying purposes
		style(): string {
			if (!this.photo?.precomputed.is_livephoto) {
				// No small variant: no placeholder background, otherwise `url(undefined)` requests `/undefined`.
				const smallUrl = this.photo?.size_variants.small?.url;
				return smallUrl === undefined || smallUrl === null ? "" : `background-image: url(${smallUrl})`;
			}
			if (this.photo?.size_variants.medium !== null) {
				return `width: ${this.photo?.size_variants.medium.width}px; height: ${this.photo?.size_variants.medium.height}px`;
			}
			if (this.photo?.size_variants.original === null) {
				return "";
			}
			return `width: ${this.photo?.size_variants.original.width}px; height: ${this.photo?.size_variants.original.height}px`;
		},
		/** FR-082-08: a 360° still photo the browser can draw as a sphere. */
		isSphereCapable(): boolean {
			const precomputed = this.photo?.precomputed;
			if (precomputed === undefined || !precomputed.is_360 || precomputed.is_video || precomputed.is_raw || precomputed.is_livephoto) {
				return false;
			}
			return this.sphere_failed_photo_id !== this.photo?.id && isWebGL2Supported();
		},
		isSphereFlat(): boolean {
			return this.photo !== undefined && this.sphere_flat_photo_id === this.photo.id;
		},
		/** The v8 main lightbox shows the sphere (outside the slideshow). */
		isSphereView(): boolean {
			return this.isSphereCapable && !this.isSphereFlat;
		},
		/** FR-103-07: the shown photo has no size variants yet (its details are on their way). */
		isDetailPending(): boolean {
			return this.photo !== undefined && isDetailPending(this.photo);
		},
		imageViewMode(): ImageViewMode {
			if (this.photo === undefined) {
				return ImageViewMode.Original;
			}
			return imageViewModeOf(this.photo, useLycheeStateStore().is_photo_viewer_highest_quality_enabled);
		},
		srcSetMedium(): string {
			return this.photo === undefined ? "" : srcSetMediumOf(this.photo);
		},
		sizesMedium(): string {
			return this.photo === undefined ? "" : sizesMediumOf(this.photo);
		},
		previousStyle(): string {
			const photosState = usePhotosStore();
			if (!this.hasPrevious || photosState.photos.length === 0) {
				return "";
			}

			const previousId = this.photo?.previous_photo_id;
			const previousPhoto = photosState.photos.find((p) => p.id === previousId);
			if (previousPhoto === undefined) {
				return "";
			}
			return neighbourStyle(previousPhoto);
		},

		nextStyle(): string {
			const photosState = usePhotosStore();
			if (!this.hasNext || photosState.photos.length === 0) {
				return "";
			}

			const nextId = this.photo?.next_photo_id;
			const nextPhoto = photosState.photos.find((p) => p.id === nextId);
			if (nextPhoto === undefined) {
				return "";
			}
			return neighbourStyle(nextPhoto);
		},
	},
});
