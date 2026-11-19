/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import {
	ASSUMED_SCROLLBAR_WIDTH,
	albumGapForBreakpoint,
	computeAlbumContainerWidth,
	computeAlbumTileGeometry,
	resolveBreakpoint,
	type AlbumTileSettings,
} from "@/v8/composables/album/albumTileWidth";
import { buildVirtualAlbumRows } from "@/v8/composables/album/virtualAlbumRows";

const DEFAULTS: AlbumTileSettings = { minWidth: 175, perRowMobile: 3, gap: 16 };

/** Desktop window with the nav closed, as in spec Appendix A. */
function geometryForWindow(viewportWidth: number, settings: Partial<AlbumTileSettings> = {}) {
	const breakpoint = resolveBreakpoint(viewportWidth);
	const containerWidth = computeAlbumContainerWidth(viewportWidth, breakpoint, ASSUMED_SCROLLBAR_WIDTH, 0);
	return computeAlbumTileGeometry(containerWidth, breakpoint, { ...DEFAULTS, ...settings });
}

describe("computeAlbumTileGeometry – minimum width rule (FR-110-01, ADR-110-01)", () => {
	it.each([
		[768, 3, 224],
		[1024, 5, 179],
		[1280, 6, 190],
		[1440, 7, 183],
		[1920, 9, 192],
		[2560, 13, 177],
		[3840, 19, 184],
	])("window %i px at min 175 gives %i columns of %i px (Appendix A)", (viewportWidth, columns, tileWidth) => {
		const geometry = geometryForWindow(viewportWidth);
		expect(geometry.itemsPerRow).toBe(columns);
		expect(Math.round(geometry.tileWidth)).toBe(tileWidth);
	});

	it("fits exactly N tiles at the boundary, N − 1 one pixel below it", () => {
		// md: gap 16. Four 175 px tiles and three gaps: 748 px.
		expect(computeAlbumTileGeometry(748, "md", DEFAULTS)).toEqual({ itemsPerRow: 4, tileWidth: 175, gap: 16 });
		expect(computeAlbumTileGeometry(747, "md", DEFAULTS).itemsPerRow).toBe(3);
	});

	it("keeps min ≤ tileWidth < min + (min + gap) / columns", () => {
		for (let containerWidth = 600; containerWidth <= 3000; containerWidth += 7) {
			for (const minWidth of [80, 175, 300, 480]) {
				for (const gapSetting of [0, 16, 48]) {
					const { tileWidth, itemsPerRow, gap } = computeAlbumTileGeometry(containerWidth, "xl", {
						...DEFAULTS,
						minWidth,
						gap: gapSetting,
					});
					if (itemsPerRow === 1 && containerWidth < minWidth) {
						continue; // narrower than one tile: the tile is the container
					}
					expect(tileWidth).toBeGreaterThanOrEqual(minWidth - 1e-6);
					expect(tileWidth).toBeLessThan(minWidth + (minWidth + gap) / itemsPerRow);
				}
			}
		}
	});

	it("gives a single full-width column when the minimum exceeds half the container", () => {
		expect(computeAlbumTileGeometry(577, "sm", { ...DEFAULTS, minWidth: 300 })).toEqual({ itemsPerRow: 1, tileWidth: 577, gap: 8 });
	});

	it("never widens a lone album to fill the row (S-110-14)", () => {
		const geometry = geometryForWindow(1440);
		const rows = buildVirtualAlbumRows(
			["only"],
			[{ bucketId: "all", label: "", startIndex: 0, count: 1 }],
			false,
			geometry.itemsPerRow,
			geometry.tileWidth,
			1,
			geometry.gap,
		);
		const box = rows.getTileBox(0);
		expect(geometry.itemsPerRow).toBe(7);
		expect(box.left).toBe(0);
		expect(Math.round(box.width)).toBe(183);
	});

	it("uses the mobile column count below sm, whatever the minimum (FR-110-03)", () => {
		expect(computeAlbumTileGeometry(358, "", { ...DEFAULTS, minWidth: 1000, perRowMobile: 2 }).itemsPerRow).toBe(2);
		expect(computeAlbumTileGeometry(358, "", { ...DEFAULTS, minWidth: 50 }).itemsPerRow).toBe(3);
		expect(computeAlbumTileGeometry(358, "", { ...DEFAULTS, perRowMobile: 1 })).toEqual({ itemsPerRow: 1, tileWidth: 358, gap: 4 });
	});

	it("falls back to 175 for a non-positive minimum", () => {
		expect(computeAlbumTileGeometry(1377, "2xl", { ...DEFAULTS, minWidth: 0 })).toEqual(computeAlbumTileGeometry(1377, "2xl", DEFAULTS));
	});
});

describe("albumGapForBreakpoint – album gap rule (FR-110-16, Q-110-05 A)", () => {
	it("gives the former 4/8/16 px for the default 16 (S-110-22)", () => {
		expect(albumGapForBreakpoint("", 16)).toBe(4);
		expect(albumGapForBreakpoint("sm", 16)).toBe(8);
		for (const bp of ["md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl"]) {
			expect(albumGapForBreakpoint(bp, 16)).toBe(16);
		}
	});

	it("uses the value from md, half at sm, a quarter below sm, rounded", () => {
		expect(albumGapForBreakpoint("md", 30)).toBe(30);
		expect(albumGapForBreakpoint("sm", 30)).toBe(15);
		expect(albumGapForBreakpoint("", 30)).toBe(8);
		expect(albumGapForBreakpoint("sm", 5)).toBe(3);
		expect(albumGapForBreakpoint("", 0)).toBe(0);
		expect(albumGapForBreakpoint("xl", 0)).toBe(0);
	});

	it("falls back to 16 for a negative or non-finite value", () => {
		expect(albumGapForBreakpoint("xl", -3)).toBe(16);
		expect(albumGapForBreakpoint("sm", Number.NaN)).toBe(8);
	});

	it("drives the column count (S-110-23)", () => {
		const wide = geometryForWindow(1440, { gap: 30 });
		expect(wide).toEqual({ itemsPerRow: 6, tileWidth: 204.5, gap: 30 });
		const touching = geometryForWindow(1440, { gap: 0 });
		expect(touching.itemsPerRow).toBe(7);
		expect(touching.gap).toBe(0);
		expect(touching.tileWidth).toBeCloseTo(1377 / 7, 6);
	});

	it("keeps the default geometry identical to the former fixed gaps", () => {
		expect(geometryForWindow(390).gap).toBe(4);
		expect(geometryForWindow(700).gap).toBe(8);
		expect(geometryForWindow(1440).gap).toBe(16);
	});
});

describe("computeAlbumContainerWidth", () => {
	it("subtracts the wrapper padding, scrollbar and side chrome", () => {
		expect(computeAlbumContainerWidth(1440, "2xl", 15, 0)).toBe(1440 - 48 - 15);
		expect(computeAlbumContainerWidth(1440, "2xl", 15, 64)).toBe(1440 - 48 - 15 - 64);
		expect(computeAlbumContainerWidth(390, "", 0, 0)).toBe(390 - 32);
		expect(computeAlbumContainerWidth(20, "", 15, 0)).toBe(0);
	});
});
