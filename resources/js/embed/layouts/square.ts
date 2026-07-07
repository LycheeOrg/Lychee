import type { Photo, PositionedPhoto, LayoutResult } from "@/embed/types";
import { square } from "./wasmLayouts";

/**
 * Square Layout Algorithm
 *
 * All photos displayed as perfect squares in a regular grid pattern.
 * Photos are cropped to fit squares, row heights are synchronized.
 *
 * Requires the WASM module to already be initialized (see `initLayouts` in ./wasmLayouts).
 */

/**
 * Square layout implementation
 *
 * @param photos Array of photos to layout
 * @param containerWidth Available container width
 * @param targetSize Target size for squares (default: 200px)
 * @param gap Gap between squares (default: 8px)
 * @returns Layout result with positioned photos and container height
 */
export function layoutSquare(photos: Photo[], containerWidth: number, targetSize: number = 200, gap: number = 8): LayoutResult {
	if (photos.length === 0 || containerWidth <= 0) {
		return { photos: [], containerHeight: 0 };
	}

	// Aspect ratios are ignored by the square algorithm; only the count matters.
	const ratios = new Float64Array(photos.length).fill(1);

	const geometry = square(ratios, containerWidth, targetSize, gap);

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
