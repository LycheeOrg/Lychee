/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it, vi } from "vite-plus/test";

// The WASM primitives do not run under jsdom: a stand-in that records its
// arguments and returns one row per tile, enough to check what the preview
// asks for and that it returns exactly what the gallery's function returns.
vi.mock("@/v8/layouts/wasmLayouts", () => {
	const fake = (ratios: Float64Array, width: number, target: number, gap: number) => ({
		containerHeight: ratios.length * (target + gap) - gap,
		boxes: Array.from(ratios, (r, i) => ({ top: i * (target + gap), left: 0, width: Math.min(width, target * r), height: target })),
	});
	return {
		justified: vi.fn(fake),
		masonry: vi.fn(fake),
		grid: vi.fn(fake),
		square: vi.fn(fake),
		initLayouts: vi.fn(() => Promise.resolve()),
	};
});

import { justified, masonry } from "@/v8/layouts/wasmLayouts";
import { computeBucketLayout } from "@/v8/composables/photo/analyticPhotoLayout";
import { ASSUMED_SCROLLBAR_WIDTH, computeAlbumContainerWidth, computeAlbumTileGeometry } from "@/v8/composables/album/albumTileWidth";
import { DEFAULT_GALLERY_SIZES, type GallerySizes } from "@/v8/utils/gallerySizes";
import {
	PREVIEW_PRESETS,
	computePhotoContainerWidth,
	computePreviewGeometry,
	photoTargetAndGap,
	previewScale,
} from "@/v8/utils/galleryPreviewGeometry";

const SIZES: GallerySizes = { ...DEFAULT_GALLERY_SIZES };

describe("computePreviewGeometry (NFR-110-01, FR-110-09)", () => {
	it("lays albums out with the gallery's own geometry", () => {
		const geometry = computePreviewGeometry({
			viewportWidth: 1440,
			scrollbarWidth: ASSUMED_SCROLLBAR_WIDTH,
			sizes: SIZES,
			layout: "justified",
			albumCount: 12,
			photoRatios: [],
		});
		const direct = computeAlbumTileGeometry(computeAlbumContainerWidth(1440, "xl", ASSUMED_SCROLLBAR_WIDTH, 0), "xl", {
			minWidth: 175,
			perRowMobile: 3,
			gap: 16,
		});
		expect(geometry.breakpoint).toBe("xl");
		expect(geometry.albums.itemsPerRow).toBe(direct.itemsPerRow);
		expect(geometry.albums.tileWidth).toBe(direct.tileWidth);
		expect(geometry.albums.boxes).toHaveLength(12);
		expect(geometry.albums.boxes[7]).toEqual({ top: direct.tileWidth + direct.gap, left: 0, width: direct.tileWidth, height: direct.tileWidth });
		expect(geometry.albums.height).toBeCloseTo(2 * direct.tileWidth + direct.gap, 6);
	});

	it("uses the selected aspect ratio for album height (S-110-08)", () => {
		const square = computePreviewGeometry({
			viewportWidth: 1440,
			scrollbarWidth: 15,
			sizes: SIZES,
			layout: "grid",
			albumCount: 3,
			photoRatios: [],
		});
		const portrait = computePreviewGeometry({
			viewportWidth: 1440,
			scrollbarWidth: 15,
			sizes: { ...SIZES, default_album_thumb_aspect_ratio: "2/3" },
			layout: "grid",
			albumCount: 3,
			photoRatios: [],
		});
		expect(portrait.albums.itemsPerRow).toBe(square.albums.itemsPerRow);
		expect(portrait.albums.tileHeight).toBeCloseTo(square.albums.tileHeight * 1.5, 6);
	});

	it("uses the mobile column count at the Phone preset (S-110-13)", () => {
		const phone = PREVIEW_PRESETS.find((p) => p.id === "phone");
		expect(phone?.width).toBe(390);
		const geometry = computePreviewGeometry({
			viewportWidth: 390,
			scrollbarWidth: 0,
			sizes: { ...SIZES, number_albums_per_row_mobile: 2 },
			layout: "square",
			albumCount: 5,
			photoRatios: [],
		});
		expect(geometry.breakpoint).toBe("");
		expect(geometry.albums.itemsPerRow).toBe(2);
	});

	it("shows 13 albums per row at the Wide preset (S-110-13)", () => {
		const geometry = computePreviewGeometry({
			viewportWidth: 2560,
			scrollbarWidth: 15,
			sizes: SIZES,
			layout: "justified",
			albumCount: 20,
			photoRatios: [],
		});
		expect(geometry.albums.itemsPerRow).toBe(13);
	});

	it("lays photos out with computeBucketLayout and the layout's own target", () => {
		const ratios = [1.5, 0.75, 1, 16 / 9];
		const sizes: GallerySizes = { ...SIZES, photo_layout_masonry_column_width: 333, photo_layout_gap: 7 };
		const geometry = computePreviewGeometry({
			viewportWidth: 1440,
			scrollbarWidth: 15,
			sizes,
			layout: "masonry",
			albumCount: 1,
			photoRatios: ratios,
		});
		const width = computePhotoContainerWidth(1440, 15);
		expect(vi.mocked(masonry)).toHaveBeenLastCalledWith(Float64Array.from(ratios), width, 333, 7);
		const direct = computeBucketLayout("masonry", ratios, width, 333, 7);
		expect(geometry.photos.boxes).toEqual(direct.boxes);
		expect(geometry.photos.height).toBe(direct.containerHeight);
		expect(geometry.photos.rows).toBe(4);
	});

	it("feeds justified its row height", () => {
		computePreviewGeometry({ viewportWidth: 1024, scrollbarWidth: 15, sizes: SIZES, layout: "justified", albumCount: 1, photoRatios: [1] });
		expect(vi.mocked(justified)).toHaveBeenLastCalledWith(Float64Array.from([1]), computePhotoContainerWidth(1024, 15), 320, 12);
	});
});

describe("album gap in the preview (FR-110-16)", () => {
	it("uses album_thumb_gap like the gallery", () => {
		const geometry = computePreviewGeometry({
			viewportWidth: 1440,
			scrollbarWidth: 15,
			sizes: { ...SIZES, album_thumb_gap: 30 },
			layout: "grid",
			albumCount: 8,
			photoRatios: [],
		});
		expect(geometry.albums.itemsPerRow).toBe(6);
		expect(geometry.albums.gap).toBe(30);
		expect(geometry.albums.boxes[1].left).toBeCloseTo(204.5 + 30, 6);
		expect(geometry.albums.boxes[6].top).toBeCloseTo(204.5 + 30, 6);
	});

	it("scales it at the Phone preset", () => {
		const geometry = computePreviewGeometry({
			viewportWidth: 390,
			scrollbarWidth: 0,
			sizes: { ...SIZES, album_thumb_gap: 30 },
			layout: "grid",
			albumCount: 3,
			photoRatios: [],
		});
		expect(geometry.albums.gap).toBe(8);
	});
});

describe("photoTargetAndGap", () => {
	it("maps each layout to its setting, gap shared (FR-110-08)", () => {
		expect(photoTargetAndGap("justified", SIZES)).toEqual({ target: 320, gap: 12 });
		expect(photoTargetAndGap("masonry", SIZES)).toEqual({ target: 300, gap: 12 });
		expect(photoTargetAndGap("grid", SIZES)).toEqual({ target: 250, gap: 12 });
		expect(photoTargetAndGap("square", SIZES)).toEqual({ target: 200, gap: 12 });
	});
});

describe("computePhotoContainerWidth", () => {
	it("subtracts the container padding by breakpoint and the scrollbar twice, as PhotoGridVirtual measures it", () => {
		expect(computePhotoContainerWidth(390, 0)).toBe(390 - 32);
		expect(computePhotoContainerWidth(820, 0)).toBe(820 - 48);
		expect(computePhotoContainerWidth(1440, 15)).toBe(1440 - 64 - 30);
	});
});

describe("previewScale", () => {
	it("scales down to fit, never up", () => {
		expect(previewScale(1440, 720)).toBe(0.5);
		expect(previewScale(390, 1000)).toBe(1);
		expect(previewScale(1440, 0)).toBe(1);
	});
});
