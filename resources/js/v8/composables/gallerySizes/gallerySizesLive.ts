/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef } from "vue";
import { defineStore } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLayoutStore, type ExtendedPhotoLayoutType } from "@/stores/LayoutState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useSearchStore } from "@/stores/SearchState";
import { aspectRatioToCss, type GallerySizes } from "@/v8/utils/gallerySizes";

/** Routes showing album or photo tiles: where the live drawer is offered and stays open (FR-110-13, FR-110-15). */
export const LIVE_GALLERY_ROUTES: readonly string[] = ["gallery", "album", "flow-album", "timeline", "search"];

export type LiveGalleryCounts = { rootAlbums: number; childAlbums: number; searchAlbums: number; photos: number };
export type LiveGallerySections = { albums: boolean; photos: boolean };

/**
 * Which drawer sections apply to the page (FR-110-14): only settings whose
 * tiles are shown, so the drawer never offers a control with no visible effect.
 * The root gallery never counts photos: `photosStore` may still hold the
 * previous album's.
 */
export function liveGallerySections(routeName: string | symbol | null | undefined, counts: LiveGalleryCounts): LiveGallerySections {
	switch (routeName) {
		case "gallery":
			return { albums: counts.rootAlbums > 0, photos: false };
		case "album":
		case "flow-album":
			return { albums: counts.childAlbums > 0, photos: counts.photos > 0 };
		case "timeline":
			return { albums: false, photos: counts.photos > 0 };
		case "search":
			return { albums: counts.searchAlbums > 0, photos: counts.photos > 0 };
		default:
			return { albums: false, photos: false };
	}
}

/** Spotlight offers the live drawer to admins on the gallery routes only (FR-110-13). */
export function canOfferLiveGallerySizes(routeName: string | symbol | null | undefined, canEditSettings: boolean): boolean {
	return canEditSettings && typeof routeName === "string" && LIVE_GALLERY_ROUTES.includes(routeName);
}

/**
 * Feature 110 (DO-110-04, FR-110-14): while the live drawer is open, the
 * gallery reads the drawer's values instead of the stored ones. Every reader
 * of a size setting goes through the composables below (album grids and
 * tiles, `PhotoGridVirtual.vue`, `dragAndSelect.ts`), so one override moves
 * tiles and drag-selection boxes together. `null` = no override.
 */
export const useGallerySizesLiveStore = defineStore("gallery-sizes-live", {
	state: () => ({
		override: null as GallerySizes | null,
		layoutBefore: null as ExtendedPhotoLayoutType | null,
	}),
	getters: {
		isActive: (state) => state.override !== null,
	},
	actions: {
		/** Remembers the photo layout shown before the drawer opened (restored on close). */
		start(layout: ExtendedPhotoLayoutType): void {
			this.layoutBefore = layout;
		},
		apply(sizes: GallerySizes): void {
			this.override = { ...sizes };
		},
		/** Clears the override; returns the layout to restore. */
		stop(): ExtendedPhotoLayoutType | null {
			const layout = this.layoutBefore;
			this.override = null;
			this.layoutBefore = null;
			return layout;
		},
	},
});

/** The default ratio replaces `base` unless the album has a ratio of its own (as on the server). */
export function effectiveAspectRatio(
	base: App.Enum.AspectRatioCSSType | undefined,
	override: GallerySizes | null,
	hasOwnRatio: boolean,
): App.Enum.AspectRatioCSSType | undefined {
	if (override === null || hasOwnRatio) {
		return base;
	}
	return aspectRatioToCss(override.default_album_thumb_aspect_ratio);
}

/** `album_thumb_min_width`, `number_albums_per_row_mobile` and `album_thumb_gap` as the album grids use them. */
export function useEffectiveAlbumTileSettings(): { minWidth: ComputedRef<number>; perRowMobile: ComputedRef<1 | 2 | 3>; gap: ComputedRef<number> } {
	const lycheeStore = useLycheeStateStore();
	const live = useGallerySizesLiveStore();
	return {
		minWidth: computed(() => live.override?.album_thumb_min_width ?? lycheeStore.album_thumb_min_width),
		perRowMobile: computed(() => live.override?.number_albums_per_row_mobile ?? lycheeStore.number_albums_per_row_mobile),
		gap: computed(() => live.override?.album_thumb_gap ?? lycheeStore.album_thumb_gap),
	};
}

/**
 * Album tile aspect ratio. `album`: the open album's children; `root`: the
 * root gallery; `nearest`: the chain the tile components always used (open
 * album, else root, else square).
 */
export function useAlbumThumbAspectRatio(scope: "album" | "root"): ComputedRef<App.Enum.AspectRatioCSSType | undefined>;
export function useAlbumThumbAspectRatio(scope: "nearest"): ComputedRef<App.Enum.AspectRatioCSSType>;
export function useAlbumThumbAspectRatio(scope: "album" | "root" | "nearest"): ComputedRef<App.Enum.AspectRatioCSSType | undefined> {
	const albumStore = useAlbumStore();
	const albumsStore = useAlbumsStore();
	const live = useGallerySizesLiveStore();

	return computed(() => {
		const albumRatio = albumStore.config?.album_thumb_css_aspect_ratio;
		const rootRatio = albumsStore.rootConfig?.album_thumb_css_aspect_ratio;
		const hasOwnRatio = (albumStore.modelAlbum?.editable?.aspect_ratio ?? null) !== null;
		switch (scope) {
			case "album":
				return effectiveAspectRatio(albumRatio, live.override, hasOwnRatio);
			case "root":
				return effectiveAspectRatio(rootRatio, live.override, false);
			case "nearest":
				if (albumStore.config !== undefined) {
					return effectiveAspectRatio(albumRatio, live.override, hasOwnRatio) ?? "aspect-square";
				}
				return effectiveAspectRatio(rootRatio ?? "aspect-square", live.override, false) ?? "aspect-square";
		}
	});
}

/** `LayoutState.config` with the live values on top. */
export function usePhotoLayoutConfig(): ComputedRef<App.Http.Resources.GalleryConfigs.PhotoLayoutConfig | undefined> {
	const layoutStore = useLayoutStore();
	const live = useGallerySizesLiveStore();
	return computed(() => {
		const o = live.override;
		if (o === null) {
			return layoutStore.config;
		}
		return {
			photo_layout_gap: o.photo_layout_gap,
			photo_layout_justified_row_height: o.photo_layout_justified_row_height,
			photo_layout_masonry_column_width: o.photo_layout_masonry_column_width,
			photo_layout_grid_column_width: o.photo_layout_grid_column_width,
			photo_layout_square_column_width: o.photo_layout_square_column_width,
		};
	});
}

/**
 * `liveGallerySections()` fed with the lists the grids render from: root
 * panels (own, shared, pinned, smart, tag, person), the open album's
 * sub-albums, the visible search albums, and the photos of the album,
 * timeline or search.
 */
export function useLiveGallerySections(routeName: () => string | symbol | null | undefined): ComputedRef<LiveGallerySections> {
	const albumsStore = useAlbumsStore();
	const photosStore = usePhotosStore();
	const searchStore = useSearchStore();
	const lycheeStore = useLycheeStateStore();
	return computed(() =>
		liveGallerySections(routeName(), {
			rootAlbums:
				albumsStore.albums.length +
				albumsStore.sharedAlbumsV3.length +
				albumsStore.pinnedAlbums.length +
				albumsStore.smartAlbumsPanel.length +
				albumsStore.tagAlbumsPanel.length +
				albumsStore.personAlbumsPanel.length,
			childAlbums: albumsStore.albums.length,
			searchAlbums: searchStore.albumTilesV3.filter((album) => !album.is_nsfw || lycheeStore.are_nsfw_visible).length,
			photos: photosStore.photos.length,
		}),
	);
}
