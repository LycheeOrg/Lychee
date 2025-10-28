import type { Breakpoint } from "../types";

/**
 * Default responsive breakpoints for column calculation
 */
export const DEFAULT_BREAKPOINTS: Breakpoint[] = [
	{ minWidth: 0, columns: 1 },
	{ minWidth: 480, columns: 2 },
	{ minWidth: 768, columns: 3 },
	{ minWidth: 1024, columns: 4 },
	{ minWidth: 1280, columns: 5 },
	{ minWidth: 1536, columns: 6 },
];

/**
 * Calculate number of columns based on available width and target column width
 *
 * This implements responsive column calculation that:
 * 1. Calculates ideal number of columns: floor(availableWidth / targetColumnWidth)
 * 2. Ensures at least 1 column
 * 3. Distributes any remainder space equally among columns
 *
 * @param availableWidth Total available width in pixels
 * @param targetColumnWidth Target width for each column in pixels
 * @param spacing Gap between columns in pixels
 * @returns Object with column count and actual column width
 */
export function calculateColumns(
	availableWidth: number,
	targetColumnWidth: number,
	spacing: number,
): {
	columns: number;
	columnWidth: number;
} {
	// Calculate total spacing (n-1 gaps for n columns)
	// We need to account for spacing when calculating how many columns fit
	const calculateForColumns = (cols: number): number => {
		const totalSpacing = (cols - 1) * spacing;
		return (availableWidth - totalSpacing) / cols;
	};

	// Start with ideal number of columns
	let columns = Math.floor((availableWidth + spacing) / (targetColumnWidth + spacing));

	// Ensure at least 1 column
	columns = Math.max(1, columns);

	// Calculate actual column width with remainder distributed
	const columnWidth = calculateForColumns(columns);

	// If columns are getting too narrow (< 80% of target), reduce column count
	if (columnWidth < targetColumnWidth * 0.8 && columns > 1) {
		columns--;
		return {
			columns,
			columnWidth: calculateForColumns(columns),
		};
	}

	return {
		columns,
		columnWidth,
	};
}

/**
 * Calculate number of columns based on responsive breakpoints
 *
 * @param availableWidth Total available width in pixels
 * @param breakpoints Array of breakpoint configurations (default: DEFAULT_BREAKPOINTS)
 * @returns Number of columns for the given width
 */
export function calculateColumnsFromBreakpoints(availableWidth: number, breakpoints: Breakpoint[] = DEFAULT_BREAKPOINTS): number {
	// Sort breakpoints by minWidth descending to find the first match
	const sorted = [...breakpoints].sort((a, b) => b.minWidth - a.minWidth);

	for (const breakpoint of sorted) {
		if (availableWidth >= breakpoint.minWidth) {
			return breakpoint.columns;
		}
	}

	// Fallback to first breakpoint (smallest)
	return breakpoints[0]?.columns ?? 1;
}

/**
 * Calculate actual column widths for a given container width and column count
 *
 * Distributes any remainder pixels across columns to ensure they fill
 * the container width exactly.
 *
 * @param containerWidth Total container width in pixels
 * @param columns Number of columns
 * @param spacing Gap between columns in pixels
 * @returns Array of column widths (length = columns)
 */
export function distributeColumnWidths(containerWidth: number, columns: number, spacing: number): number[] {
	const totalSpacing = (columns - 1) * spacing;
	const availableWidth = containerWidth - totalSpacing;
	const baseWidth = Math.floor(availableWidth / columns);
	const remainder = availableWidth - baseWidth * columns;

	// Create array of base widths
	const widths = Array(columns).fill(baseWidth);

	// Distribute remainder pixels across first N columns
	for (let i = 0; i < remainder; i++) {
		widths[i]++;
	}

	return widths;
}

/**
 * Calculate row height for a justified layout
 *
 * Given a set of photo aspect ratios and a target row height,
 * calculates the actual row height that will make photos fit
 * exactly in the available width.
 *
 * @param aspectRatios Array of photo aspect ratios (width/height)
 * @param availableWidth Available width for the row
 * @param spacing Gap between photos in pixels
 * @param targetHeight Target row height (used as starting point)
 * @returns Calculated row height
 */
export function calculateJustifiedRowHeight(aspectRatios: number[], availableWidth: number, spacing: number, targetHeight: number): number {
	if (aspectRatios.length === 0) {
		return targetHeight;
	}

	// Calculate total spacing
	const totalSpacing = (aspectRatios.length - 1) * spacing;
	const availableForPhotos = availableWidth - totalSpacing;

	// Sum of aspect ratios gives us the ratio of total width to height
	const totalAspectRatio = aspectRatios.reduce((sum, ar) => sum + ar, 0);

	// Solve for height: availableForPhotos = height * totalAspectRatio
	const height = availableForPhotos / totalAspectRatio;

	// Return the calculated height, bounded to reasonable limits
	return Math.max(100, Math.min(height, targetHeight * 2));
}

/**
 * Get photo aspect ratio from size variant data
 *
 * @param width Photo width
 * @param height Photo height
 * @returns Aspect ratio (width/height), defaults to 1 if invalid
 */
export function getAspectRatio(width: number, height: number): number {
	if (height === 0 || !isFinite(width) || !isFinite(height)) {
		return 1; // Fallback to square
	}
	return width / height;
}
