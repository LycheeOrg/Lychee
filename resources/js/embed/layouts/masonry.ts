import type { Photo, PositionedPhoto, LayoutResult } from "@/embed/types";
import { getAspectRatio, getSafeDimensions } from "@/embed/utils/columns";
import { masonry } from "./wasmLayouts";

/**
 * Masonry Layout Algorithm (Pinterest-style)
 *
 * Photos arranged in columns with variable heights.
 * Aspect ratios preserved, items placed in shortest available column.
 * Creates organic, flowing appearance.
 *
 * Requires the WASM module to already be initialized (see `initLayouts` in ./wasmLayouts).
 */

/**
 * Masonry layout implementation
 *
 * @param photos Array of photos to layout
 * @param containerWidth Available container width
 * @param targetColumnWidth Target width for columns (default: 300px)
 * @param gap Gap between items (default: 8px)
 * @returns Layout result with positioned photos and container height
 */
export function layoutMasonry(photos: Photo[], containerWidth: number, targetColumnWidth: number = 300, gap: number = 8): LayoutResult {
	if (photos.length === 0) {
		return { photos: [], containerHeight: 0 };
	}

	const aspectRatios = Float64Array.from(
		photos.map((photo) => {
			const { width, height } = getSafeDimensions(photo.size_variants);
			return getAspectRatio(width, height);
		}),
	);

	const geometry = masonry(aspectRatios, containerWidth, targetColumnWidth, gap);

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
