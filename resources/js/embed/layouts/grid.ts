import type { Photo, PositionedPhoto, LayoutResult } from "@/embed/types";
import { getAspectRatio, getSafeDimensions } from "@/embed/utils/columns";
import { grid } from "./wasmLayouts";

/**
 * Grid Layout Algorithm
 *
 * Regular grid with fixed column widths.
 * Aspect ratios preserved within columns.
 * Row heights synchronized across columns.
 * Balanced between uniformity and aspect ratios.
 *
 * Requires the WASM module to already be initialized (see `initLayouts` in ./wasmLayouts).
 */

/**
 * Grid layout implementation
 *
 * @param photos Array of photos to layout
 * @param containerWidth Available container width
 * @param targetColumnWidth Target width for columns (default: 250px)
 * @param gap Gap between items (default: 8px)
 * @returns Layout result with positioned photos and container height
 */
export function layoutGrid(photos: Photo[], containerWidth: number, targetColumnWidth: number = 250, gap: number = 8): LayoutResult {
	if (photos.length === 0) {
		return { photos: [], containerHeight: 0 };
	}

	const aspectRatios = Float64Array.from(
		photos.map((photo) => {
			const { width, height } = getSafeDimensions(photo.size_variants);
			return getAspectRatio(width, height);
		}),
	);

	const geometry = grid(aspectRatios, containerWidth, targetColumnWidth, gap);

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
