/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { beforeEach, describe, expect, it } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";
import {
	canOfferLiveGallerySizes,
	effectiveAspectRatio,
	liveGallerySections,
	useAlbumThumbAspectRatio,
	useEffectiveAlbumTileSettings,
	useGallerySizesLiveStore,
	usePhotoLayoutConfig,
} from "@/v8/composables/gallerySizes/gallerySizesLive";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { DEFAULT_GALLERY_SIZES, type GallerySizes } from "@/v8/utils/gallerySizes";

const LIVE: GallerySizes = {
	album_thumb_min_width: 300,
	album_thumb_gap: 6,
	default_album_thumb_aspect_ratio: "2/3",
	number_albums_per_row_mobile: 1,
	photo_layout_gap: 3,
	photo_layout_justified_row_height: 111,
	photo_layout_masonry_column_width: 222,
	photo_layout_grid_column_width: 333,
	photo_layout_square_column_width: 444,
};

describe("canOfferLiveGallerySizes (FR-110-13)", () => {
	it.each(["gallery", "album", "flow-album", "timeline", "search"])("offers the drawer to admins on %s", (name) => {
		expect(canOfferLiveGallerySizes(name, true)).toBe(true);
		expect(canOfferLiveGallerySizes(name, false)).toBe(false);
	});
	it.each(["settings", "admin-dashboard", "gallery-sizes", "people", undefined, null])("never on %s", (name) => {
		expect(canOfferLiveGallerySizes(name, true)).toBe(false);
	});
});

describe("liveGallerySections (FR-110-14: only sections whose tiles are on the page)", () => {
	const none = { rootAlbums: 0, childAlbums: 0, searchAlbums: 0, photos: 0 };

	it("root gallery: albums only, never photos (the photo store may hold the previous album's)", () => {
		expect(liveGallerySections("gallery", { ...none, rootAlbums: 3, photos: 8 })).toEqual({ albums: true, photos: false });
		expect(liveGallerySections("gallery", none)).toEqual({ albums: false, photos: false });
	});

	it.each(["album", "flow-album"])("%s: sub-albums and photos", (name) => {
		expect(liveGallerySections(name, { ...none, childAlbums: 2 })).toEqual({ albums: true, photos: false });
		expect(liveGallerySections(name, { ...none, photos: 5 })).toEqual({ albums: false, photos: true });
		expect(liveGallerySections(name, { ...none, rootAlbums: 4 })).toEqual({ albums: false, photos: false });
	});

	it("timeline: photos only", () => {
		expect(liveGallerySections("timeline", { ...none, rootAlbums: 3, childAlbums: 3, photos: 1 })).toEqual({ albums: false, photos: true });
	});

	it("search: result albums and photos", () => {
		expect(liveGallerySections("search", { ...none, searchAlbums: 1 })).toEqual({ albums: true, photos: false });
		expect(liveGallerySections("search", { ...none, childAlbums: 2, photos: 3 })).toEqual({ albums: false, photos: true });
	});

	it("other routes: nothing", () => {
		expect(liveGallerySections("settings", { rootAlbums: 1, childAlbums: 1, searchAlbums: 1, photos: 1 })).toEqual({
			albums: false,
			photos: false,
		});
		expect(liveGallerySections(undefined, { rootAlbums: 1, childAlbums: 1, searchAlbums: 1, photos: 1 })).toEqual({
			albums: false,
			photos: false,
		});
	});
});

describe("effectiveAspectRatio", () => {
	it("keeps the base without override", () => {
		expect(effectiveAspectRatio("aspect-3x2", null, false)).toBe("aspect-3x2");
	});
	it("applies the override unless the album has its own ratio (FR-110-14)", () => {
		expect(effectiveAspectRatio("aspect-3x2", LIVE, false)).toBe("aspect-2x3");
		expect(effectiveAspectRatio("aspect-video", LIVE, true)).toBe("aspect-video");
	});
});

describe("live override (DO-110-04)", () => {
	beforeEach(() => {
		setActivePinia(createPinia());
	});

	it("remembers the layout shown before opening and returns it on stop", () => {
		const live = useGallerySizesLiveStore();
		live.start("masonry");
		live.apply(LIVE);
		expect(live.override).toEqual(LIVE);
		expect(live.stop()).toBe("masonry");
		expect(live.override).toBeNull();
		expect(live.isActive).toBe(false);
	});

	it("feeds the album tile settings", () => {
		const lychee = useLycheeStateStore();
		lychee.album_thumb_min_width = 175;
		lychee.album_thumb_gap = 16;
		lychee.number_albums_per_row_mobile = 3;
		const settings = useEffectiveAlbumTileSettings();
		expect(settings.minWidth.value).toBe(175);
		expect(settings.perRowMobile.value).toBe(3);
		expect(settings.gap.value).toBe(16);
		useGallerySizesLiveStore().apply(LIVE);
		expect(settings.minWidth.value).toBe(300);
		expect(settings.perRowMobile.value).toBe(1);
		expect(settings.gap.value).toBe(6);
	});

	it("feeds the photo layout config", () => {
		useLayoutStore().config = { ...DEFAULT_GALLERY_SIZES };
		const config = usePhotoLayoutConfig();
		expect(config.value?.photo_layout_gap).toBe(12);
		useGallerySizesLiveStore().apply(LIVE);
		expect(config.value).toEqual({
			photo_layout_gap: 3,
			photo_layout_justified_row_height: 111,
			photo_layout_masonry_column_width: 222,
			photo_layout_grid_column_width: 333,
			photo_layout_square_column_width: 444,
		});
	});

	it("feeds the album aspect ratio by scope; an album's own ratio wins (S-110-20)", () => {
		useAlbumsStore().rootConfig = { album_thumb_css_aspect_ratio: "aspect-square" } as App.Http.Resources.GalleryConfigs.RootConfig;
		const album = useAlbumStore();
		album.config = { album_thumb_css_aspect_ratio: "aspect-video" } as App.Http.Resources.GalleryConfigs.AlbumConfig;
		album.modelAlbum = { editable: { aspect_ratio: "16/9" } } as unknown as App.Http.Resources.Models.HeadAlbumResource;

		const root = useAlbumThumbAspectRatio("root");
		const inAlbum = useAlbumThumbAspectRatio("album");
		const nearest = useAlbumThumbAspectRatio("nearest");
		useGallerySizesLiveStore().apply(LIVE);

		expect(root.value).toBe("aspect-2x3");
		expect(inAlbum.value).toBe("aspect-video");
		expect(nearest.value).toBe("aspect-video");

		album.modelAlbum = { editable: { aspect_ratio: null } } as unknown as App.Http.Resources.Models.HeadAlbumResource;
		expect(inAlbum.value).toBe("aspect-2x3");
		expect(nearest.value).toBe("aspect-2x3");
	});

	it("falls back like the tiles did without override", () => {
		const nearest = useAlbumThumbAspectRatio("nearest");
		expect(nearest.value).toBe("aspect-square");
		useAlbumsStore().rootConfig = { album_thumb_css_aspect_ratio: "aspect-3x2" } as App.Http.Resources.GalleryConfigs.RootConfig;
		expect(nearest.value).toBe("aspect-3x2");
		expect(useAlbumThumbAspectRatio("album").value).toBeUndefined();
	});
});
