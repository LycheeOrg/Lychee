/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it } from "vite-plus/test";
import { detailWindowIds, ImageViewMode, imageViewModeOf, isDetailPending, preloadSourceOf } from "@/utils/photoView";

type Photo = App.Http.Resources.Models.PhotoResource;
type Variant = App.Http.Resources.Models.SizeVariantResource;

type Variants = App.Http.Resources.Models.SizeVariantsResouce;

const EMPTY: Variants = {
	raw: null,
	original: null,
	medium2x: null,
	medium: null,
	small2x: null,
	small: null,
	thumb2x: null,
	thumb: null,
	placeholder: null,
};

function variant(name: string, width: number, height: number): Variant {
	return { url: `/uploads/${name}.jpg`, width, height } as unknown as Variant;
}

function photo(overrides: { id?: string; is_video?: boolean; is_raw?: boolean; is_livephoto?: boolean; size_variants?: Partial<Variants> }): Photo {
	return {
		id: overrides.id ?? "p",
		precomputed: { is_video: overrides.is_video ?? false, is_raw: overrides.is_raw ?? false, is_livephoto: overrides.is_livephoto ?? false },
		size_variants: { ...EMPTY, ...overrides.size_variants },
	} as unknown as Photo;
}

const MERGED = {
	original: variant("original", 4000, 3000),
	medium: variant("medium", 1200, 900),
	medium2x: variant("medium2x", 2400, 1800),
	thumb: variant("thumb", 200, 200),
};

describe("isDetailPending (FR-103-07)", () => {
	it("is pending for a tile whose details are not merged", () => {
		expect(isDetailPending(photo({}))).toBe(true);
	});

	it("is not pending once its size variants are there", () => {
		expect(isDetailPending(photo({ size_variants: MERGED }))).toBe(false);
		expect(isDetailPending(photo({ size_variants: { original: { ...MERGED.original, url: null } as Variant } }))).toBe(false);
	});
});

describe("detailWindowIds (FR-103-08, S-103-19)", () => {
	const photos = Array.from({ length: 30 }, (_, i) => photo({ id: `p${i}` }));

	it("takes the shown photo, the 10 next and the 5 previous", () => {
		expect(detailWindowIds(photos, "p10")).toEqual(["p10", ...Array.from({ length: 10 }, (_, i) => `p${11 + i}`), "p9", "p8", "p7", "p6", "p5"]);
	});

	it("stops at the ends of the list", () => {
		expect(detailWindowIds(photos, "p1")).toEqual(["p1", ...Array.from({ length: 10 }, (_, i) => `p${2 + i}`), "p0"]);
		expect(detailWindowIds(photos, "p28")).toEqual(["p28", "p29", "p27", "p26", "p25", "p24", "p23"]);
	});

	it("is empty for an unknown photo", () => {
		expect(detailWindowIds(photos, "nope")).toEqual([]);
	});
});

describe("preloadSourceOf (FR-103-08, S-103-19)", () => {
	it("preloads the medium srcset the view would show", () => {
		const source = preloadSourceOf(photo({ size_variants: MERGED }), false);
		expect(source).toEqual({
			src: "/uploads/medium.jpg",
			srcset: "/uploads/medium.jpg 1200w, /uploads/medium2x.jpg 2400w",
			sizes: "min(100vw, 1.3333 * 100vh)",
		});
	});

	it("preloads the original when the view shows the original", () => {
		expect(preloadSourceOf(photo({ size_variants: MERGED }), true)).toEqual({ src: "/uploads/original.jpg", srcset: "", sizes: "" });
		expect(preloadSourceOf(photo({ size_variants: { original: MERGED.original, thumb: MERGED.thumb } }), false)).toEqual({
			src: "/uploads/original.jpg",
			srcset: "",
			sizes: "",
		});
	});

	it("preloads nothing for videos, raw files and pending details", () => {
		expect(preloadSourceOf(photo({ is_video: true, size_variants: MERGED }), false)).toBeNull();
		expect(preloadSourceOf(photo({ is_raw: true, size_variants: { original: MERGED.original, thumb: MERGED.thumb } }), false)).toBeNull();
		expect(preloadSourceOf(photo({}), false)).toBeNull();
	});
});

describe("imageViewModeOf", () => {
	it("follows the photo type and the available variants", () => {
		expect(imageViewModeOf(photo({ is_video: true, size_variants: MERGED }), false)).toBe(ImageViewMode.Video);
		expect(imageViewModeOf(photo({ size_variants: MERGED }), false)).toBe(ImageViewMode.Medium);
		expect(imageViewModeOf(photo({ size_variants: MERGED }), true)).toBe(ImageViewMode.Original);
		expect(imageViewModeOf(photo({ is_livephoto: true, size_variants: MERGED }), false)).toBe(ImageViewMode.LivePhotoMedium);
	});
});
