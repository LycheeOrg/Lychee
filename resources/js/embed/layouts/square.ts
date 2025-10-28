import type { Photo, PositionedPhoto, LayoutResult } from "../types";
import { getAspectRatio } from "../utils/columns";

/**
 * Square Layout Algorithm
 *
 * All photos displayed as perfect squares in a regular grid pattern.
 * Photos are cropped to fit squares, row heights are synchronized.
 */

interface ColumnData {
	left: number;
	height: number;
}

/**
 * Calculate number of columns and final square size
 *
 * @param containerWidth Available container width
 * @param targetSize Target square size
 * @param gap Gap between squares
 * @returns Column count and adjusted square size
 */
function calculateSquareColumns(containerWidth: number, targetSize: number, gap: number): { columns: number; finalSquareSize: number } {
	// How many squares fit?
	const columns = Math.max(1, Math.floor((containerWidth + gap) / (targetSize + gap)));

	// Remaining space after fitting squares + gaps
	const usedSpace = columns * targetSize + (columns - 1) * gap;
	const remainingSpace = containerWidth - usedSpace;

	// Distribute remaining space evenly across all squares
	const spread = Math.ceil(remainingSpace / columns);

	// Final square size after distributing extra space
	const finalSquareSize = targetSize + spread;

	return { columns, finalSquareSize };
}

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
	if (photos.length === 0) {
		return { photos: [], containerHeight: 0 };
	}

	// Calculate columns and final square size
	const { columns, finalSquareSize } = calculateSquareColumns(containerWidth, targetSize, gap);

	// Initialize column tracking
	const columnData: ColumnData[] = Array.from({ length: columns }, (_, i) => ({
		left: i * (finalSquareSize + gap),
		height: 0,
	}));

	// Position photos
	const positionedPhotos: PositionedPhoto[] = photos.map((photo, index) => {
		const columnIndex = index % columns;
		const column = columnData[columnIndex];

		// Synchronize row heights at start of each new row
		if (index % columns === 0 && index > 0) {
			const maxHeight = Math.max(...columnData.map((c) => c.height));
			columnData.forEach((c) => (c.height = maxHeight));
		}

		// Create positioned photo
		const positioned: PositionedPhoto = {
			...photo,
			position: {
				width: finalSquareSize,
				height: finalSquareSize, // Square!
				left: column.left,
				top: column.height,
			},
		};

		// Update column height
		column.height += finalSquareSize + gap;

		return positioned;
	});

	// Calculate final container height
	const containerHeight = Math.max(...columnData.map((c) => c.height)) - gap;

	return {
		photos: positionedPhotos,
		containerHeight,
	};
}
