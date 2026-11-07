/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computeAlbumContainerWidth, computeAlbumTileGeometry, resolveBreakpoint } from "@/v8/composables/album/albumTileWidth";
import { buildVirtualAlbumRows, type TileBox } from "@/v8/composables/album/virtualAlbumRows";
import { computeBucketLayout, type PhotoBox } from "@/v8/composables/photo/analyticPhotoLayout";
import { aspectRatioCssToNumber } from "@/v8/utils/aspectRatioNumber";
import { aspectRatioToCss, type GallerySizes } from "@/v8/utils/gallerySizes";

/**
 * Feature 110 (FR-110-09, NFR-110-01): geometry of the preview page for a
 * simulated window width. Albums go through the gallery's own
 * `computeAlbumContainerWidth` → `computeAlbumTileGeometry` →
 * `buildVirtualAlbumRows`; photos through `computeBucketLayout` (the WASM
 * primitives `PhotoGridVirtual.vue` uses). Only the width differs from the
 * gallery: nav panel closed, no date scrubber.
 */

export type PreviewWidthPreset = "phone" | "tablet" | "laptop" | "desktop" | "wide" | "window";

/** Fixed presets; `touch` decides the scrollbar (none on touch devices). */
export const PREVIEW_PRESETS: readonly { id: Exclude<PreviewWidthPreset, "window">; width: number; touch: boolean }[] = [
	{ id: "phone", width: 390, touch: true },
	{ id: "tablet", width: 820, touch: true },
	{ id: "laptop", width: 1440, touch: false },
	{ id: "desktop", width: 1920, touch: false },
	{ id: "wide", width: 2560, touch: false },
];

/** `UContainer` padding (`px-4 sm:px-6 lg:px-8`) around `PhotoGridVirtual.vue`. */
function photoContainerPadding(viewportWidth: number): number {
	if (viewportWidth >= 1024) {
		return 32;
	}
	return viewportWidth >= 640 ? 24 : 16;
}

/**
 * Width `PhotoGridVirtual.vue` lays photos out in: it measures its element
 * (inside the `UContainer` padding, beside the page scrollbar) and subtracts
 * `ASSUMED_SCROLLBAR_WIDTH` again, so the scrollbar counts twice.
 */
export function computePhotoContainerWidth(viewportWidth: number, scrollbarWidth: number): number {
	return Math.max(0, viewportWidth - 2 * photoContainerPadding(viewportWidth) - 2 * scrollbarWidth);
}

/** Left offset of each grid inside the window: the album wrapper and photo container paddings. */
export function previewOffsets(viewportWidth: number, breakpoint: string): { albums: number; photos: number } {
	return { albums: breakpoint === "" ? 16 : 24, photos: photoContainerPadding(viewportWidth) };
}

/**
 * Target per layout, the gap shared by all four: the one mapping used by
 * `PhotoGridVirtual.vue`, `dragAndSelect.ts` and the preview.
 */
export function photoTargetAndGap(
	layout: App.Enum.PhotoLayoutType,
	sizes: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig,
): { target: number; gap: number } {
	const gap = sizes.photo_layout_gap;
	switch (layout) {
		case "justified":
			return { target: sizes.photo_layout_justified_row_height, gap };
		case "masonry":
			return { target: sizes.photo_layout_masonry_column_width, gap };
		case "grid":
			return { target: sizes.photo_layout_grid_column_width, gap };
		case "square":
			return { target: sizes.photo_layout_square_column_width, gap };
	}
}

export type PreviewGeometryInput = {
	viewportWidth: number;
	scrollbarWidth: number;
	sizes: GallerySizes;
	layout: App.Enum.PhotoLayoutType;
	albumCount: number;
	photoRatios: number[];
};

export type PreviewGeometry = {
	breakpoint: string;
	offsets: { albums: number; photos: number };
	albums: { itemsPerRow: number; tileWidth: number; tileHeight: number; gap: number; boxes: TileBox[]; height: number };
	photos: { boxes: PhotoBox[]; height: number; rows: number };
};

export function computePreviewGeometry(input: PreviewGeometryInput): PreviewGeometry {
	const breakpoint = resolveBreakpoint(input.viewportWidth);
	const albumContainer = computeAlbumContainerWidth(input.viewportWidth, breakpoint, input.scrollbarWidth, 0);
	const album = computeAlbumTileGeometry(albumContainer, breakpoint, {
		minWidth: input.sizes.album_thumb_min_width,
		perRowMobile: input.sizes.number_albums_per_row_mobile,
		gap: input.sizes.album_thumb_gap,
	});
	const aspect = aspectRatioCssToNumber(aspectRatioToCss(input.sizes.default_album_thumb_aspect_ratio));

	const ids = Array.from({ length: input.albumCount }, (_, i) => String(i));
	const rows = buildVirtualAlbumRows(
		ids,
		[{ bucketId: "all", label: "", startIndex: 0, count: ids.length }],
		false,
		album.itemsPerRow,
		album.tileWidth,
		aspect,
		album.gap,
	);
	const albumBoxes = ids.map((_, i) => rows.getTileBox(i));

	const { target, gap } = photoTargetAndGap(input.layout, input.sizes);
	const photoLayout = computeBucketLayout(
		input.layout,
		input.photoRatios,
		computePhotoContainerWidth(input.viewportWidth, input.scrollbarWidth),
		target,
		gap,
	);

	return {
		breakpoint,
		offsets: previewOffsets(input.viewportWidth, breakpoint),
		albums: {
			itemsPerRow: album.itemsPerRow,
			tileWidth: album.tileWidth,
			tileHeight: aspect > 0 ? album.tileWidth / aspect : album.tileWidth,
			gap: album.gap,
			boxes: albumBoxes,
			height: rows.rowHeights.reduce((sum, h) => sum + h, 0),
		},
		photos: {
			boxes: photoLayout.boxes,
			height: photoLayout.containerHeight,
			rows: new Set(photoLayout.boxes.map((b) => b.top)).size,
		},
	};
}

/** Scale that fits a window of `viewportWidth` into a pane of `paneWidth`, never above 1. */
export function previewScale(viewportWidth: number, paneWidth: number): number {
	if (paneWidth <= 0 || viewportWidth <= 0) {
		return 1;
	}
	return Math.min(1, paneWidth / viewportWidth);
}
