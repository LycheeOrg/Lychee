/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import source from "./AlbumThumbVirtual.vue?raw";

/**
 * Feature 110 (FR-110-12, ADR-110-01): album tile covers do not pin a size
 * variant, so `Thumb.vue` picks `small2x` when `devicePixelRatio > 1`
 * (FR-102-06), as photo tiles do.
 */
describe("AlbumThumbVirtual.vue covers", () => {
	const thumbs = source.match(/<Thumb\b[^>]*>/g) ?? [];

	it("render the cover through Thumb", () => {
		expect(thumbs.length).toBeGreaterThanOrEqual(3);
	});

	it("never pin the size variant", () => {
		expect(thumbs.filter((tag) => /\btype=/.test(tag))).toEqual([]);
	});
});
