import type { Photo, PositionedPhoto, LayoutResult } from "@/embed/types";
import { getAspectRatio, getSafeDimensions } from "@/embed/utils/columns";

/**
 * Filmstrip Layout Algorithm
 *
 * Single large photo display (main viewer) with horizontal thumbnail strip below.
 * Main viewer takes 70-80% of container height.
 * Thumbnail strip takes remaining 20-30% of container height.
 * Active thumbnail highlighted, navigation controls provided.
 *
 * Structure:
 * ┌─────────────────────────────────────┐
 * │         Main Photo Viewer           │  70-80%
 * │         (Large Photo)               │  height
 * ├─────────────────────────────────────┤
 * │ [◀] [thumb][thumb][thumb][thumb][▶] │  20-30%
 * └─────────────────────────────────────┘  height
 */

export interface FilmstripLayoutResult {
	/** Main photo viewer dimensions */
	mainViewer: {
		width: number;
		height: number;
		top: number;
		left: number;
	};
	/** Thumbnail strip dimensions */
	thumbnailStrip: {
		width: number;
		height: number;
		top: number;
		left: number;
	};
	/** Positioned thumbnails within the strip */
	thumbnails: Array<{
		photo: Photo;
		position: {
			width: number;
			height: number;
			left: number;
			top: number;
		};
	}>;
	/** Total container height */
	containerHeight: number;
	/** Currently active photo index */
	activeIndex: number;
}

/**
 * Filmstrip layout implementation
 *
 * @param photos Array of photos to layout
 * @param containerWidth Available container width
 * @param containerHeight Available container height (must be specified for filmstrip)
 * @param thumbnailHeight Height of thumbnail strip (default: 100px)
 * @param gap Gap between elements (default: 8px)
 * @param activeIndex Index of currently active photo (default: 0)
 * @returns Filmstrip layout result with main viewer and thumbnail positions
 */
export function layoutFilmstrip(
	photos: Photo[],
	containerWidth: number,
	containerHeight: number,
	thumbnailHeight: number = 100,
	gap: number = 8,
	activeIndex: number = 0,
): FilmstripLayoutResult {
	if (photos.length === 0) {
		return {
			mainViewer: { width: containerWidth, height: containerHeight, top: 0, left: 0 },
			thumbnailStrip: { width: containerWidth, height: 0, top: containerHeight, left: 0 },
			thumbnails: [],
			containerHeight: containerHeight,
			activeIndex: 0,
		};
	}

	// Calculate main viewer height (remaining space after thumbnail strip)
	const mainHeight = containerHeight - thumbnailHeight - gap;

	// Main viewer takes full width
	const mainViewer = {
		width: containerWidth,
		height: mainHeight,
		top: 0,
		left: 0,
	};

	// Thumbnail strip positioned below main viewer
	const thumbnailStrip = {
		width: containerWidth,
		height: thumbnailHeight,
		top: mainHeight + gap,
		left: 0,
	};

	// Position thumbnails within the strip
	// Each thumbnail maintains aspect ratio and fits within thumbnailHeight
	// Precompute thumbnail widths to avoid O(n²) complexity
	const thumbWidths = photos.map((photo) => {
		const { width, height } = getSafeDimensions(photo.size_variants);
		const aspectRatio = getAspectRatio(width, height);
		return Math.floor(thumbnailHeight * aspectRatio);
	});

	// Calculate positions by accumulating left offset in O(n)
	let leftAcc = 0;
	const thumbnails = photos.map((photo, index) => {
		const thumbWidth = thumbWidths[index];
		const position = {
			width: thumbWidth,
			height: thumbnailHeight,
			left: leftAcc,
			top: 0, // Relative to thumbnail strip
		};

		// Update accumulated left position for next thumbnail
		leftAcc += thumbWidth + gap;

		return {
			photo,
			position,
		};
	});

	return {
		mainViewer,
		thumbnailStrip,
		thumbnails,
		containerHeight: containerHeight,
		activeIndex: Math.max(0, Math.min(activeIndex, photos.length - 1)),
	};
}

/**
 * Convert filmstrip layout to standard LayoutResult for compatibility
 * This treats filmstrip as if thumbnails were the positioned photos
 *
 * @param filmstripResult Filmstrip layout result
 * @returns Standard layout result
 */
export function filmstripToLayoutResult(filmstripResult: FilmstripLayoutResult): LayoutResult {
	const positionedPhotos: PositionedPhoto[] = filmstripResult.thumbnails.map((thumb) => ({
		...thumb.photo,
		position: {
			...thumb.position,
			// Adjust top to be relative to container (not thumbnail strip)
			top: filmstripResult.thumbnailStrip.top + thumb.position.top,
		},
	}));

	return {
		photos: positionedPhotos,
		containerHeight: filmstripResult.containerHeight,
	};
}
