import justifiedLayout from 'justified-layout';
import type { Photo, PositionedPhoto, LayoutResult } from '../types';
import { getAspectRatio } from '../utils/columns';

/**
 * Justified Layout Algorithm (Flickr-style)
 *
 * Photos arranged in rows with consistent height.
 * Aspect ratios preserved.
 * Photos scaled to fit perfectly within rows.
 * Uses Flickr's justified-layout library v4.1.0.
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
export function layoutJustified(
	photos: Photo[],
	containerWidth: number,
	targetRowHeight: number = 320,
	gap: number = 8,
): LayoutResult {
	if (photos.length === 0) {
		return { photos: [], containerHeight: 0 };
	}

	// Extract aspect ratios from photos
	const aspectRatios = photos.map((photo) =>
		getAspectRatio(photo.size_variants.original.width, photo.size_variants.original.height),
	);

	// Calculate layout geometry using justified-layout library
	const geometry = justifiedLayout(aspectRatios, {
		containerWidth: containerWidth,
		containerPadding: 0,
		targetRowHeight: targetRowHeight,
		boxSpacing: {
			horizontal: gap,
			vertical: gap,
		},
		// Ensure last row is justified (not left-aligned)
		fullWidthBreakoutRowCadence: false,
	});

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
