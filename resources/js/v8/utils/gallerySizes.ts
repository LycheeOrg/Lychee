/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Feature 110 (DO-110-03): the 9 size settings the preview page and the live
 * drawer edit, with their control ranges (UI-110-05). Pure: no store, no
 * request, so page, drawer and tests share one definition.
 */
export type GallerySizes = {
	album_thumb_min_width: number;
	album_thumb_gap: number;
	default_album_thumb_aspect_ratio: App.Enum.AspectRatioType;
	number_albums_per_row_mobile: 1 | 2 | 3;
	photo_layout_gap: number;
	photo_layout_justified_row_height: number;
	photo_layout_masonry_column_width: number;
	photo_layout_grid_column_width: number;
	photo_layout_square_column_width: number;
};

export type GallerySizeKey = keyof GallerySizes;

export type NumericGallerySizeKey = Exclude<GallerySizeKey, "default_album_thumb_aspect_ratio" | "number_albums_per_row_mobile">;

export const GALLERY_SIZE_KEYS: readonly GallerySizeKey[] = [
	"album_thumb_min_width",
	"album_thumb_gap",
	"default_album_thumb_aspect_ratio",
	"number_albums_per_row_mobile",
	"photo_layout_gap",
	"photo_layout_justified_row_height",
	"photo_layout_masonry_column_width",
	"photo_layout_grid_column_width",
	"photo_layout_square_column_width",
];

/** Migration defaults; used only for a key the server does not return or returns unparsable. */
export const DEFAULT_GALLERY_SIZES: GallerySizes = {
	album_thumb_min_width: 175,
	album_thumb_gap: 16,
	default_album_thumb_aspect_ratio: "1/1",
	number_albums_per_row_mobile: 3,
	photo_layout_gap: 12,
	photo_layout_justified_row_height: 320,
	photo_layout_masonry_column_width: 300,
	photo_layout_grid_column_width: 250,
	photo_layout_square_column_width: 200,
};

export type SizeRange = { min: number; max: number; step: number };

export const GALLERY_SIZE_RANGES: Record<NumericGallerySizeKey, SizeRange> = {
	album_thumb_min_width: { min: 80, max: 480, step: 5 },
	album_thumb_gap: { min: 0, max: 48, step: 1 },
	photo_layout_gap: { min: 1, max: 48, step: 1 },
	photo_layout_justified_row_height: { min: 80, max: 800, step: 10 },
	photo_layout_masonry_column_width: { min: 80, max: 800, step: 10 },
	photo_layout_grid_column_width: { min: 80, max: 800, step: 10 },
	photo_layout_square_column_width: { min: 80, max: 800, step: 10 },
};

export const ASPECT_RATIOS: readonly App.Enum.AspectRatioType[] = ["1/1", "3/2", "2/3", "5/4", "4/5", "16/9"];

const ASPECT_RATIO_CSS: Record<App.Enum.AspectRatioType, App.Enum.AspectRatioCSSType> = {
	"5/4": "aspect-5x4",
	"4/5": "aspect-4x5",
	"3/2": "aspect-3x2",
	"2/3": "aspect-2x3",
	"1/1": "aspect-square",
	"16/9": "aspect-video",
};

/** Same mapping as `App\Enum\AspectRatioType::css()`. */
export function aspectRatioToCss(ratio: App.Enum.AspectRatioType): App.Enum.AspectRatioCSSType {
	return ASPECT_RATIO_CSS[ratio];
}

/** Inverse of `aspectRatioToCss()`; `undefined` stays `undefined`. */
export function cssToAspectRatio(css: App.Enum.AspectRatioCSSType | undefined): App.Enum.AspectRatioType | undefined {
	return ASPECT_RATIOS.find((ratio) => ASPECT_RATIO_CSS[ratio] === css);
}

const LAYOUT_OF_KEY: Partial<Record<GallerySizeKey, App.Enum.PhotoLayoutType>> = {
	photo_layout_justified_row_height: "justified",
	photo_layout_masonry_column_width: "masonry",
	photo_layout_grid_column_width: "grid",
	photo_layout_square_column_width: "square",
};

/** The photo layout a setting belongs to, `null` for shared or album settings (FR-110-08). */
export function layoutOfGallerySize(key: GallerySizeKey): App.Enum.PhotoLayoutType | null {
	return LAYOUT_OF_KEY[key] ?? null;
}

export function isNumericGallerySize(key: GallerySizeKey): key is NumericGallerySizeKey {
	return key in GALLERY_SIZE_RANGES;
}

/** Control input → integer within the UI-110-05 range; `NaN` becomes the minimum. */
export function clampGallerySize(key: NumericGallerySizeKey, value: number): number {
	const range = GALLERY_SIZE_RANGES[key];
	if (!Number.isFinite(value)) {
		return range.min;
	}
	return Math.min(range.max, Math.max(range.min, Math.round(value)));
}

function isAspectRatio(value: string): value is App.Enum.AspectRatioType {
	return (ASPECT_RATIOS as readonly string[]).includes(value);
}

function parseNonNegativeInt(value: string): number | null {
	return /^\d+$/.test(value) ? Number.parseInt(value, 10) : null;
}

function parsePositiveInt(value: string): number | null {
	const parsed = parseNonNegativeInt(value);
	return parsed !== null && parsed > 0 ? parsed : null;
}

function parseOne<K extends GallerySizeKey>(key: K, value: string): GallerySizes[K] | null {
	if (key === "default_album_thumb_aspect_ratio") {
		return (isAspectRatio(value) ? value : null) as GallerySizes[K] | null;
	}
	if (key === "album_thumb_gap") {
		// `int` setting: 0 lets the album tiles touch (FR-110-16).
		return parseNonNegativeInt(value) as GallerySizes[K] | null;
	}
	const parsed = parsePositiveInt(value);
	if (key === "number_albums_per_row_mobile") {
		return (parsed !== null && parsed <= 3 ? parsed : null) as GallerySizes[K] | null;
	}
	return parsed as GallerySizes[K] | null;
}

export type ParsedGallerySizes = {
	values: GallerySizes;
	/** Keys the server returned, i.e. the ones this admin may edit (the mobile setting is SE-only). */
	editable: GallerySizeKey[];
};

/**
 * Stored values from `GET /Settings`, searched by key in every category.
 * A stored value outside the control range is kept as is (UI-110-05); an
 * absent or unparsable one keeps `fallback`'s value.
 */
export function parseGallerySizes(categories: App.Http.Resources.Models.ConfigCategoryResource[], fallback: GallerySizes): ParsedGallerySizes {
	const byKey = new Map<string, string>();
	for (const cat of categories) {
		for (const config of cat.configs) {
			byKey.set(config.key, config.value);
		}
	}

	const values: GallerySizes = { ...fallback };
	const editable: GallerySizeKey[] = [];
	for (const key of GALLERY_SIZE_KEYS) {
		const raw = byKey.get(key);
		if (raw === undefined) {
			continue;
		}
		editable.push(key);
		const parsed = parseOne(key, raw);
		if (parsed !== null) {
			(values as Record<GallerySizeKey, unknown>)[key] = parsed;
		}
	}
	return { values, editable };
}

/** `setConfigs` payload: the editable keys whose draft differs from the stored value. */
export function changedGallerySizes(
	stored: GallerySizes,
	draft: GallerySizes,
	editable: readonly GallerySizeKey[],
): { key: string; value: string }[] {
	return GALLERY_SIZE_KEYS.filter((key) => editable.includes(key) && stored[key] !== draft[key]).map((key) => ({ key, value: String(draft[key]) }));
}
