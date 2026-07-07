import type { Photo, PositionedPhoto, LayoutResult } from "@/embed/types";
import { getAspectRatio, getSafeDimensions } from "@/embed/utils/columns";
import { justified } from "./wasmLayouts";

/**
 * Justified Layout Algorithm (Flickr-style)
 *
 * Photos arranged in rows with consistent height.
 * Aspect ratios preserved.
 * Photos scaled to fit perfectly within rows.
 * Uses the @lychee-org/layouts WASM implementation of Flickr's justified-layout algorithm.
 *
 * Requires the WASM module to already be initialized (see `initLayouts` in ./wasmLayouts).
 */

/**
 * Justified layout implementation
 *
 * @param photos Array of photos to layout
 * @param containerWidth Available container width
 * @param targetRowHeight Target height for rows (default: 320px)
 * @param gap Gap between items (default: 8px)
 * @returns Layout result with positioned photos and container height
 */
export function layoutJustified(photos: Photo[], containerWidth: number, targetRowHeight: number = 320, gap: number = 8): LayoutResult {
	if (photos.length === 0) {
		return { photos: [], containerHeight: 0 };
	}

	// Extract aspect ratios from photos
	const aspectRatios = Float64Array.from(
		photos.map((photo) => {
			const { width, height } = getSafeDimensions(photo.size_variants);
			return getAspectRatio(width, height);
		}),
	);

	// Calculate layout geometry using the WASM justified layout engine
	const geometry = justified(aspectRatios, containerWidth, targetRowHeight, gap);

	// Position photos according to calculated geometry
	const positionedPhotos: PositionedPhoto[] = photos.map((photo, index) => {
		const box = geometry.boxes[index];

		return {
			...photo,
			position: {
				width: box.width,
				height: box.height,
				left: box.left,
				top: box.top,
			},
		};
	});

	return {
		photos: positionedPhotos,
		containerHeight: geometry.containerHeight,
	};
}
