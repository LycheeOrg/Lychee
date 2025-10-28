import type { Photo, PositionedPhoto, LayoutResult } from "../types";
import { getAspectRatio } from "../utils/columns";

/**
 * Grid Layout Algorithm
 *
 * Regular grid with fixed column widths.
 * Aspect ratios preserved within columns.
 * Row heights synchronized across columns.
 * Balanced between uniformity and aspect ratios.
 */

interface ColumnData {
	left: number;
	height: number;
}

/**
 * Calculate number of columns and final column width
 *
 * @param containerWidth Available container width
 * @param targetWidth Target column width
 * @param gap Gap between columns
 * @returns Column count and adjusted width
 */
function calculateGridColumns(containerWidth: number, targetWidth: number, gap: number): { columns: number; finalWidth: number } {
	// How many columns fit?
	const columns = Math.max(1, Math.floor((containerWidth + gap) / (targetWidth + gap)));

	// Remaining space after fitting columns + gaps
	const usedSpace = columns * targetWidth + (columns - 1) * gap;
	const remainingSpace = containerWidth - usedSpace;

	// Distribute remaining space evenly across all columns
	const spread = Math.ceil(remainingSpace / columns);

	// Final column width after distributing extra space
	const finalWidth = targetWidth + spread;

	return { columns, finalWidth };
}

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

	// Calculate columns and final width
	const { columns, finalWidth } = calculateGridColumns(containerWidth, targetColumnWidth, gap);

	// Initialize column tracking
	const columnData: ColumnData[] = Array.from({ length: columns }, (_, i) => ({
		left: i * (finalWidth + gap),
		height: 0,
	}));

	// Position photos using round-robin with row synchronization
	const positionedPhotos: PositionedPhoto[] = photos.map((photo, index) => {
		const columnIndex = index % columns; // Round-robin distribution
		const column = columnData[columnIndex];

		// Synchronize row heights at start of each new row
		if (index % columns === 0 && index > 0) {
			const maxHeight = Math.max(...columnData.map((c) => c.height));
			columnData.forEach((c) => (c.height = maxHeight));
		}

		// Calculate height maintaining aspect ratio
		const aspectRatio = getAspectRatio(photo.size_variants.original.width, photo.size_variants.original.height);
		const height = Math.floor(finalWidth / aspectRatio);

		// Create positioned photo
		const positioned: PositionedPhoto = {
			...photo,
			position: {
				width: finalWidth,
				height: height,
				left: column.left,
				top: column.height,
			},
		};

		// Update column height
		column.height += height + gap;

		return positioned;
	});

	// Calculate final container height
	const containerHeight = Math.max(...columnData.map((c) => c.height)) - gap;

	return {
		photos: positionedPhotos,
		containerHeight,
	};
}
