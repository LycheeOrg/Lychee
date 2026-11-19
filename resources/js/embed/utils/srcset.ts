import type { Photo, SizeVariantData } from "@/embed/types";

/**
 * Uncropped variants only: thumb/thumb2x are square crops and would show a
 * different framing than the other candidates.
 */
const SRCSET_VARIANTS = ["small", "small2x", "medium", "medium2x"] as const;

function srcsetVariants(photo: Photo): SizeVariantData[] {
	return SRCSET_VARIANTS.map((type) => photo.size_variants[type]).filter(
		(variant): variant is SizeVariantData => variant !== null && variant.url !== null && variant.width > 0 && variant.height > 0,
	);
}

/**
 * `srcset` with width descriptors, so the browser picks the variant matching
 * the rendered size and the screen's pixel density.
 */
export function getSrcset(photo: Photo): string {
	return srcsetVariants(photo)
		.map((variant) => `${variant.url} ${variant.width}w`)
		.join(", ");
}

/**
 * `sizes` for an image rendered in a box of the given CSS size.
 * With `cover`, the image overflows the box on one axis, so it needs to be as
 * wide as the larger of the two fits; with `contain`, the smaller one.
 */
export function getSizes(photo: Photo, width: number, height: number, fit: "cover" | "contain"): string {
	const variant = srcsetVariants(photo)[0];
	if (variant === undefined) {
		return "";
	}
	const heightFit = height * (variant.width / variant.height);
	const needed = fit === "cover" ? Math.max(width, heightFit) : Math.min(width, heightFit);

	return `${Math.ceil(needed)}px`;
}

/**
 * `sizes` for an image fitted (`contain`) into the viewport. Overestimates
 * when chrome around the image takes space, which only errs towards a larger
 * variant.
 */
export function getViewportSizes(photo: Photo): string {
	const variant = srcsetVariants(photo)[0];
	if (variant === undefined) {
		return "";
	}

	return `min(100vw, ${(variant.width / variant.height).toFixed(4)} * 100vh)`;
}
