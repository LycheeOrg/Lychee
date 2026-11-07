/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import {
	DEFAULT_GALLERY_SIZES,
	GALLERY_SIZE_KEYS,
	aspectRatioToCss,
	changedGallerySizes,
	clampGallerySize,
	layoutOfGallerySize,
	parseGallerySizes,
	type GallerySizes,
} from "@/v8/utils/gallerySizes";

function config(key: string, value: string): App.Http.Resources.Models.ConfigResource {
	return {
		key,
		value,
		type: "int",
		is_set: true,
		documentation: "",
		details: "",
		is_expert: false,
		require_se: false,
		order: 0,
		required_keys: [],
	};
}

function category(cat: string, configs: App.Http.Resources.Models.ConfigResource[]): App.Http.Resources.Models.ConfigCategoryResource {
	return { cat, name: cat, description: "", configs, priority: 0 } as unknown as App.Http.Resources.Models.ConfigCategoryResource;
}

const STORED: GallerySizes = {
	album_thumb_min_width: 200,
	album_thumb_gap: 0,
	default_album_thumb_aspect_ratio: "3/2",
	number_albums_per_row_mobile: 2,
	photo_layout_gap: 8,
	photo_layout_justified_row_height: 240,
	photo_layout_masonry_column_width: 280,
	photo_layout_grid_column_width: 260,
	photo_layout_square_column_width: 180,
};

describe("gallerySizes (DO-110-03)", () => {
	it("lists the 9 settings", () => {
		expect([...GALLERY_SIZE_KEYS].sort()).toEqual(Object.keys(DEFAULT_GALLERY_SIZES).sort());
		expect(GALLERY_SIZE_KEYS).toHaveLength(9);
	});

	it("parses the stored values from any category and reports the editable keys", () => {
		const parsed = parseGallerySizes(
			[
				category("Gallery", [
					config("album_thumb_min_width", "200"),
					config("album_thumb_gap", "0"),
					config("default_album_thumb_aspect_ratio", "3/2"),
					config("number_albums_per_row_mobile", "2"),
					config("photo_layout_gap", "8"),
					config("photo_layout_justified_row_height", "240"),
				]),
				category("Other", [
					config("photo_layout_masonry_column_width", "280"),
					config("photo_layout_grid_column_width", "260"),
					config("photo_layout_square_column_width", "180"),
					config("unrelated", "1"),
				]),
			],
			DEFAULT_GALLERY_SIZES,
		);
		expect(parsed.values).toEqual(STORED);
		expect([...parsed.editable].sort()).toEqual([...GALLERY_SIZE_KEYS].sort());
	});

	it("keeps the fallback for a key the server does not return (SE-only mobile setting) and marks it read-only", () => {
		const parsed = parseGallerySizes([category("Gallery", [config("album_thumb_min_width", "200")])], {
			...DEFAULT_GALLERY_SIZES,
			number_albums_per_row_mobile: 1,
		});
		expect(parsed.values.number_albums_per_row_mobile).toBe(1);
		expect(parsed.editable).toEqual(["album_thumb_min_width"]);
	});

	it("keeps a stored out-of-range value as is, falls back on unparsable ones", () => {
		const parsed = parseGallerySizes(
			[
				category("Gallery", [
					config("album_thumb_min_width", "900"),
					config("photo_layout_gap", "abc"),
					config("default_album_thumb_aspect_ratio", "7/3"),
					config("number_albums_per_row_mobile", "5"),
				]),
			],
			DEFAULT_GALLERY_SIZES,
		);
		expect(parsed.values.album_thumb_min_width).toBe(900);
		expect(parsed.values.photo_layout_gap).toBe(DEFAULT_GALLERY_SIZES.photo_layout_gap);
		expect(parsed.values.default_album_thumb_aspect_ratio).toBe(DEFAULT_GALLERY_SIZES.default_album_thumb_aspect_ratio);
		expect(parsed.values.number_albums_per_row_mobile).toBe(DEFAULT_GALLERY_SIZES.number_albums_per_row_mobile);
	});

	it("accepts a 0 album gap, rejects a negative one (FR-110-16)", () => {
		const parsed = parseGallerySizes([category("Gallery", [config("album_thumb_gap", "0")])], { ...DEFAULT_GALLERY_SIZES, album_thumb_gap: 9 });
		expect(parsed.values.album_thumb_gap).toBe(0);
		const negative = parseGallerySizes([category("Gallery", [config("album_thumb_gap", "-4")])], {
			...DEFAULT_GALLERY_SIZES,
			album_thumb_gap: 9,
		});
		expect(negative.values.album_thumb_gap).toBe(9);
		expect(DEFAULT_GALLERY_SIZES.album_thumb_gap).toBe(16);
		expect(clampGallerySize("album_thumb_gap", -2)).toBe(0);
		expect(clampGallerySize("album_thumb_gap", 99)).toBe(48);
		expect(layoutOfGallerySize("album_thumb_gap")).toBeNull();
	});

	it("clamps and rounds control input to the UI-110-05 ranges", () => {
		expect(clampGallerySize("album_thumb_min_width", 20)).toBe(80);
		expect(clampGallerySize("album_thumb_min_width", 1000)).toBe(480);
		expect(clampGallerySize("photo_layout_gap", 0)).toBe(1);
		expect(clampGallerySize("photo_layout_gap", 12.6)).toBe(13);
		expect(clampGallerySize("photo_layout_justified_row_height", Number.NaN)).toBe(80);
	});

	it("sends only the changed editable keys, as strings", () => {
		const draft: GallerySizes = {
			...STORED,
			album_thumb_min_width: 220,
			default_album_thumb_aspect_ratio: "1/1",
			number_albums_per_row_mobile: 3,
		};
		expect(changedGallerySizes(STORED, draft, ["album_thumb_min_width", "default_album_thumb_aspect_ratio"])).toEqual([
			{ key: "album_thumb_min_width", value: "220" },
			{ key: "default_album_thumb_aspect_ratio", value: "1/1" },
		]);
		expect(changedGallerySizes(STORED, STORED, [...GALLERY_SIZE_KEYS])).toEqual([]);
	});

	it("maps each layout-specific key to its layout (FR-110-08)", () => {
		expect(layoutOfGallerySize("photo_layout_justified_row_height")).toBe("justified");
		expect(layoutOfGallerySize("photo_layout_masonry_column_width")).toBe("masonry");
		expect(layoutOfGallerySize("photo_layout_grid_column_width")).toBe("grid");
		expect(layoutOfGallerySize("photo_layout_square_column_width")).toBe("square");
		expect(layoutOfGallerySize("photo_layout_gap")).toBeNull();
		expect(layoutOfGallerySize("album_thumb_min_width")).toBeNull();
	});

	it("maps aspect ratios to their CSS type as the server does", () => {
		expect(aspectRatioToCss("1/1")).toBe("aspect-square");
		expect(aspectRatioToCss("16/9")).toBe("aspect-video");
		expect(aspectRatioToCss("2/3")).toBe("aspect-2x3");
		expect(aspectRatioToCss("5/4")).toBe("aspect-5x4");
	});
});
