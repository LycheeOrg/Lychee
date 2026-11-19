/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { ALBUM_COUNT_RANGE, PHOTO_COUNT_RANGE, PHOTO_RATIO_MIX, generatePlaceholders } from "@/v8/utils/galleryPreviewPlaceholders";

describe("generatePlaceholders (FR-110-07, NFR-110-03)", () => {
	it("returns the requested counts", () => {
		const set = generatePlaceholders(1, 12, 60);
		expect(set.albums).toHaveLength(12);
		expect(set.photos).toHaveLength(60);
		expect(set.albums.map((a) => a.n)).toEqual(Array.from({ length: 12 }, (_, i) => i + 1));
	});

	it("clamps the counts to their ranges", () => {
		expect(generatePlaceholders(1, 0, 0).albums).toHaveLength(ALBUM_COUNT_RANGE.min);
		expect(generatePlaceholders(1, 0, 0).photos).toHaveLength(PHOTO_COUNT_RANGE.min);
		expect(generatePlaceholders(1, 1000, 9999).albums).toHaveLength(ALBUM_COUNT_RANGE.max);
		expect(generatePlaceholders(1, 1000, 9999).photos).toHaveLength(PHOTO_COUNT_RANGE.max);
	});

	it("is a pure function of the seed", () => {
		expect(generatePlaceholders(42, 10, 50)).toEqual(generatePlaceholders(42, 10, 50));
		const a = generatePlaceholders(42, 10, 50).photos.map((p) => p.ratio);
		const b = generatePlaceholders(43, 10, 50).photos.map((p) => p.ratio);
		expect(a).not.toEqual(b);
	});

	it("keeps the first photos when the counts change", () => {
		const small = generatePlaceholders(7, 3, 20);
		const large = generatePlaceholders(7, 30, 200);
		expect(large.photos.slice(0, 20)).toEqual(small.photos);
		expect(large.albums.slice(0, 3)).toEqual(small.albums);
	});

	it("draws photo ratios from the fixed mix only", () => {
		const allowed = new Set(PHOTO_RATIO_MIX.map((m) => m.ratio));
		const ratios = generatePlaceholders(3, 1, 500).photos.map((p) => p.ratio);
		expect(ratios.every((r) => allowed.has(r))).toBe(true);
		expect(new Set(ratios).size).toBeGreaterThan(4);
	});

	it("gives every tile a hue in [0, 360)", () => {
		const set = generatePlaceholders(9, 20, 100);
		for (const tile of [...set.albums, ...set.photos]) {
			expect(tile.hue).toBeGreaterThanOrEqual(0);
			expect(tile.hue).toBeLessThan(360);
		}
	});
});
