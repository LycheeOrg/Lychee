import type { Photo, PositionedPhoto, LayoutResult } from '../types';
import { getAspectRatio } from '../utils/columns';

/**
 * Masonry Layout Algorithm (Pinterest-style)
 *
 * Photos arranged in columns with variable heights.
 * Aspect ratios preserved, items placed in shortest available column.
 * Creates organic, flowing appearance.
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
function calculateMasonryColumns(
	containerWidth: number,
	targetWidth: number,
	gap: number,
): { columns: number; finalWidth: number } {
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
 * Find the index of the shortest column
 *
 * @param columns Array of column data
 * @returns Index of shortest column
 */
function findShortestColumn(columns: ColumnData[]): number {
	let shortestIndex = 0;
	let shortestHeight = columns[0].height;

	for (let i = 1; i < columns.length; i++) {
		if (columns[i].height < shortestHeight) {
			shortestHeight = columns[i].height;
			shortestIndex = i;
		}
	}

	return shortestIndex;
}

/**
 * Masonry layout implementation
 *
 * @param photos Array of photos to layout
 * @param containerWidth Available container width
 * @param targetColumnWidth Target width for columns (default: 300px)
 * @param gap Gap between items (default: 8px)
 * @returns Layout result with positioned photos and container height
 */
export function layoutMasonry(
	photos: Photo[],
	containerWidth: number,
	targetColumnWidth: number = 300,
	gap: number = 8,
): LayoutResult {
	if (photos.length === 0) {
		return { photos: [], containerHeight: 0 };
	}

	// Calculate columns and final width
	const { columns, finalWidth } = calculateMasonryColumns(containerWidth, targetColumnWidth, gap);

	// Initialize column tracking
	const columnData: ColumnData[] = Array.from({ length: columns }, (_, i) => ({
		left: i * (finalWidth + gap),
		height: 0,
	}));

	// Position photos using shortest column algorithm
	const positionedPhotos: PositionedPhoto[] = photos.map((photo) => {
		// Find shortest column (Pinterest algorithm)
		const columnIndex = findShortestColumn(columnData);
		const column = columnData[columnIndex];

		// Calculate height maintaining aspect ratio
		const aspectRatio = getAspectRatio(
			photo.size_variants.original.width,
			photo.size_variants.original.height,
		);
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

	// Calculate final container height (tallest column)
	const containerHeight = Math.max(...columnData.map((c) => c.height)) - gap;

	return {
		photos: positionedPhotos,
		containerHeight,
	};
}
