/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { InsightsPalette } from "@/v8/composables/insights/useInsightsTheme";

/**
 * Shared look of the square heatmaps (calendar, rhythm): rounded squares
 * separated by gaps in the card colour, empty cells in the surface colour,
 * four colour steps with a "Less … More" legend (FR-085-10, FR-085-11).
 */

export const GAP = 2;
/** Width reserved for the row labels (weekdays, years). */
export const LABEL_WIDTH = 44;

export type StepLegend = { less: string; more: string };

/** Squares separated by gaps in the card colour. */
export function cellStyle(palette: InsightsPalette) {
	return { borderColor: palette.background, borderWidth: GAP, borderRadius: 3 };
}

/**
 * Piecewise colour map: counts from 1 below `low` take the faintest step,
 * counts from `high` the full colour; 0 stays in the surface colour.
 */
export function stepMap(palette: InsightsPalette, thresholds: [number, number, number], legend: StepLegend, cell: number) {
	const low = Math.max(1, thresholds[0]);
	const medium = Math.max(low + 1, thresholds[1]);
	const high = Math.max(medium + 1, thresholds[2]);

	return {
		type: "piecewise" as const,
		orient: "horizontal" as const,
		left: LABEL_WIDTH,
		bottom: 0,
		itemWidth: cell - GAP,
		itemHeight: cell - GAP,
		itemGap: GAP,
		itemSymbol: "roundRect",
		showLabel: false,
		text: [legend.more, legend.less],
		textStyle: { color: palette.muted },
		outOfRange: { color: palette.surface },
		pieces: [
			{ min: 1, lt: low, color: palette.steps[0] },
			{ gte: low, lt: medium, color: palette.steps[1] },
			{ gte: medium, lt: high, color: palette.steps[2] },
			{ gte: high, color: palette.steps[3] },
		],
	};
}

/** Steps at a quarter, half and three quarters of the largest count, for data without configured thresholds. */
export function quarterThresholds(max: number): [number, number, number] {
	return [Math.ceil(max * 0.25), Math.ceil(max * 0.5), Math.ceil(max * 0.75)];
}
