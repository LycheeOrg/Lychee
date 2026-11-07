/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { paddedBounds, viewportContentPoints } from "@/v8/utils/mapContentBounds";

/**
 * Feature 109 (FR-109-08): the map opens fitted to its content, padded by
 * 10 % of the span on each side (v2 `Map.vue`'s rule).
 */
function photos(latitudes: number[], longitudes: number[]): App.Http.Resources.V3.MapPhotoResource {
	return {
		ids: latitudes.map((_, i) => `p${i}`),
		album_ids: latitudes.map(() => null),
		latitudes,
		longitudes,
	} as App.Http.Resources.V3.MapPhotoResource;
}

function buckets(
	latitudes: number[],
	longitudes: number[],
	singletons: App.Http.Resources.V3.MapPhotoResource = photos([], []),
): App.Http.Resources.V3.MapBucketResource {
	return {
		bucket_ids: latitudes.map((_, i) => `b${i}`),
		centroid_latitudes: latitudes,
		centroid_longitudes: longitudes,
		singleton_photos: singletons,
	} as App.Http.Resources.V3.MapBucketResource;
}

describe("paddedBounds", () => {
	it("pads the min/max box by 10 % of its span on each side", () => {
		expect(paddedBounds({ latitudes: [10, 20], longitudes: [100, 140] })).toEqual([
			[9, 96],
			[21, 144],
		]);
	});

	it("returns the point itself for a single point", () => {
		expect(paddedBounds({ latitudes: [48.85], longitudes: [2.35] })).toEqual([
			[48.85, 2.35],
			[48.85, 2.35],
		]);
	});

	it("returns undefined without points", () => {
		expect(paddedBounds({ latitudes: [], longitudes: [] })).toBeUndefined();
	});
});

describe("viewportContentPoints", () => {
	it("collects individual photos, bucket centroids and single-photo cells", () => {
		const points = viewportContentPoints(buckets([1, 2], [3, 4], photos([5], [6])), photos([7], [8]));

		expect(points.latitudes.sort()).toEqual([1, 2, 5, 7]);
		expect(points.longitudes.sort()).toEqual([3, 4, 6, 8]);
	});

	it("is empty when nothing was fetched", () => {
		expect(viewportContentPoints(undefined, undefined)).toEqual({ latitudes: [], longitudes: [] });
	});
});
