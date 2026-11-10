/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it, vi } from "vite-plus/test";
import { effect, reactive } from "vue";

// `list` mode needs no WASM layout primitive; `justified` stands in for one: 10 px per tile.
const justified = vi.hoisted(() =>
	vi.fn((ratios: Float64Array) => ({
		containerHeight: ratios.length * 10,
		boxes: Array.from(ratios, (_, i) => ({ top: i * 10, left: 0, width: 10, height: 10 })),
	})),
);
vi.mock("@/v8/layouts/wasmLayouts", () => ({ justified, square: vi.fn(), masonry: vi.fn(), grid: vi.fn() }));

import { computeTimelinePhotoLayout, computeVisiblePhotoLayout, type TimelineLayoutCacheEntry } from "@/v8/composables/photo/analyticPhotoLayout";

type Photo = App.Http.Resources.Models.PhotoResource;

function photos(n: number): Photo[] {
	return Array.from({ length: n }, (_, i) => ({ id: `p${i}`, title: `P${i}` }) as unknown as Photo);
}

const BOUNDARIES = [
	{ bucketId: "b1", label: "B1", startIndex: 0, count: 3 },
	{ bucketId: "b2", label: "B2", startIndex: 3, count: 2 },
];

function layout(list: Photo[], filtered: Set<string> | null = null) {
	return computeVisiblePhotoLayout("list", list, [1, 1, 1, 1, 1], BOUNDARIES, true, filtered !== null, filtered, 800, 0, 0);
}

describe("computeVisiblePhotoLayout (FR-103-05, S-103-14)", () => {
	it("positions every tile by its index in the photo array", () => {
		const { positioned, headerTops } = layout(photos(5));
		expect(positioned.map((p) => p.index)).toEqual([0, 1, 2, 3, 4]);
		expect(headerTops.map((h) => h.bucketId)).toEqual(["b1", "b2"]);
		expect(positioned[3].box.top).toBeGreaterThan(positioned[2].box.top);
	});

	it("keeps the photo array index of each tile under a rating filter", () => {
		const { positioned, boundaries } = layout(photos(5), new Set(["p1", "p3"]));
		expect(positioned.map((p) => p.index)).toEqual([1, 3]);
		expect(boundaries.map((b) => b.count)).toEqual([1, 1]);
	});

	it("does not depend on the photos themselves, only on the array", () => {
		const store = reactive({ photos: photos(5) });
		let runs = 0;
		effect(() => {
			runs++;
			layout(store.photos);
		});
		store.photos[2] = { id: "other" } as unknown as Photo;
		expect(runs).toBe(1);
		store.photos = photos(5);
		expect(runs).toBe(2);
	});
});

describe("computeTimelinePhotoLayout placeholders (FR-103-10, S-103-22)", () => {
	it("lays out one placeholder per distinct bucket size", () => {
		justified.mockClear();
		const counts = [3, 3, 5, 3, 5, 1];
		let start = 0;
		const boundaries = counts.map((count, i) => {
			const b = { bucketId: `d${i}`, label: `D${i}`, startIndex: start, count };
			start += count;
			return b;
		});

		const result = computeTimelinePhotoLayout(
			"justified",
			boundaries,
			Array.from({ length: start }, () => undefined),
			new Set(),
			new Map<string, TimelineLayoutCacheEntry>(),
			false,
			800,
			200,
			0,
		);

		expect(justified).toHaveBeenCalledTimes(3);
		expect(result.buckets.map((b) => b.height)).toEqual(counts.map((c) => c * 10));
	});
});
