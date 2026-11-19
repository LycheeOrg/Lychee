/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/** Parallel coordinate arrays, the shape every v3 map resource uses. */
export type MapPoints = { latitudes: number[]; longitudes: number[] };

/** Leaflet `LatLngBoundsExpression` as `[[south, west], [north, east]]`. */
export type PaddedBounds = [[number, number], [number, number]];

/**
 * Feature 109 (FR-109-08): bounds the map opens on — the points' min/max box,
 * padded by 10 % of its span on each side (v2 `Map.vue`'s rule). `undefined`
 * without points, so the caller keeps the whole-world view.
 */
export function paddedBounds(points: MapPoints): PaddedBounds | undefined {
	if (points.latitudes.length === 0 || points.longitudes.length === 0) {
		return undefined;
	}

	const minLat = points.latitudes.reduce((min, value) => Math.min(min, value), Infinity);
	const maxLat = points.latitudes.reduce((max, value) => Math.max(max, value), -Infinity);
	const minLng = points.longitudes.reduce((min, value) => Math.min(min, value), Infinity);
	const maxLng = points.longitudes.reduce((max, value) => Math.max(max, value), -Infinity);
	const padLat = 0.1 * (maxLat - minLat);
	const padLng = 0.1 * (maxLng - minLng);

	return [
		[minLat - padLat, minLng - padLng],
		[maxLat + padLat, maxLng + padLng],
	];
}

/**
 * Points of a whole-world fetch: individual photos (under the viewport cap),
 * bucket centroids and the photos of single-photo cells (over it).
 */
export function viewportContentPoints(
	buckets: App.Http.Resources.V3.MapBucketResource | undefined,
	photos: App.Http.Resources.V3.MapPhotoResource | undefined,
): MapPoints {
	return {
		latitudes: [...(photos?.latitudes ?? []), ...(buckets?.centroid_latitudes ?? []), ...(buckets?.singleton_photos.latitudes ?? [])],
		longitudes: [...(photos?.longitudes ?? []), ...(buckets?.centroid_longitudes ?? []), ...(buckets?.singleton_photos.longitudes ?? [])],
	};
}
