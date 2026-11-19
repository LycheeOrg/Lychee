/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Feature 110 (FR-110-07, DO-110-02, NFR-110-03): generated preview content.
 * No real data and no images: albums and photos are coloured blocks whose
 * photo aspect ratios come from a fixed mix typical of camera and phone
 * output. A pure function of `(seed, albumCount, photoCount)`; each list has
 * its own random stream, so tile `i` depends only on the seed and `i` and
 * changing a count keeps the tiles already shown.
 */

export type PlaceholderAlbum = { id: string; n: number; hue: number };
export type PlaceholderPhoto = { id: string; ratio: number; hue: number };
export type PreviewPlaceholders = { albums: PlaceholderAlbum[]; photos: PlaceholderPhoto[] };

export const ALBUM_COUNT_RANGE = { min: 1, max: 100, default: 12 } as const;
export const PHOTO_COUNT_RANGE = { min: 1, max: 500, default: 60 } as const;

/** Width/height ratios with their relative weight: landscape 3:2 and 4:3 most common. */
export const PHOTO_RATIO_MIX: readonly { ratio: number; weight: number }[] = [
	{ ratio: 3 / 2, weight: 6 },
	{ ratio: 4 / 3, weight: 5 },
	{ ratio: 2 / 3, weight: 3 },
	{ ratio: 3 / 4, weight: 3 },
	{ ratio: 1, weight: 2 },
	{ ratio: 16 / 9, weight: 2 },
	{ ratio: 9 / 16, weight: 1 },
	{ ratio: 3, weight: 1 },
];

const TOTAL_WEIGHT = PHOTO_RATIO_MIX.reduce((sum, m) => sum + m.weight, 0);

/** mulberry32: small, fast, good enough for layout placeholders. */
export function createRandom(seed: number): () => number {
	let state = seed >>> 0;
	return () => {
		state = (state + 0x6d2b79f5) >>> 0;
		let t = state;
		t = Math.imul(t ^ (t >>> 15), t | 1);
		t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
		return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
	};
}

function pickRatio(r: number): number {
	let threshold = r * TOTAL_WEIGHT;
	for (const m of PHOTO_RATIO_MIX) {
		threshold -= m.weight;
		if (threshold < 0) {
			return m.ratio;
		}
	}
	return PHOTO_RATIO_MIX[0].ratio;
}

function clampCount(value: number, range: { min: number; max: number }): number {
	return Number.isFinite(value) ? Math.min(range.max, Math.max(range.min, Math.round(value))) : range.min;
}

export function generatePlaceholders(seed: number, albumCount: number, photoCount: number): PreviewPlaceholders {
	const albumRandom = createRandom(seed ^ 0x5eed);
	const photoRandom = createRandom(seed);

	const albums = Array.from({ length: clampCount(albumCount, ALBUM_COUNT_RANGE) }, (_, i) => ({
		id: `album-${i + 1}`,
		n: i + 1,
		hue: Math.floor(albumRandom() * 360),
	}));
	const photos = Array.from({ length: clampCount(photoCount, PHOTO_COUNT_RANGE) }, (_, i) => {
		const ratio = pickRatio(photoRandom());
		return { id: `photo-${i + 1}`, ratio, hue: Math.floor(photoRandom() * 360) };
	});
	return { albums, photos };
}
