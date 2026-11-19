/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { EChartsOption } from "echarts";
import type { InsightsPalette } from "@/v8/composables/insights/useInsightsTheme";
import { escapeHtml, formatNumber, weekdayNames } from "../format";
import { itemValue, tooltipStyle } from "./common";
import { cellStyle, LABEL_WIDTH, quarterThresholds, stepMap, type StepLegend } from "./squares";

/** Side of one rhythm square, gap included (px). */
const CELL = 18;

/**
 * 7 × 24 grid of local capture hours, Monday on top (FR-085-11), drawn like
 * the calendar: rounded squares, four colour steps, "Less … More" legend.
 */
export function weekHourOption(weekHour: number[][], palette: InsightsPalette, legend: StepLegend, rtl: boolean): EChartsOption {
	const days = weekdayNames();
	const hours = Array.from({ length: 24 }, (_, h) => String(h));
	const data = weekHour.flatMap((row, day) => row.map((count, hour) => [hour, day, count]));
	const max = Math.max(1, ...data.map((cell) => cell[2]));

	return {
		grid: { left: rtl ? 8 : LABEL_WIDTH, right: rtl ? LABEL_WIDTH : 8, top: 4, width: 24 * CELL, height: 7 * CELL },
		tooltip: {
			...tooltipStyle(palette),
			formatter: (params) => {
				const [hour, day, count] = itemValue<[number, number, number]>(params);
				return `${escapeHtml(days[day])} ${hour}:00 – ${hour + 1}:00: <b>${formatNumber(count)}</b>`;
			},
		},
		visualMap: stepMap(palette, quarterThresholds(max), legend, CELL),
		xAxis: {
			type: "category",
			data: hours,
			inverse: rtl,
			axisLine: { show: false },
			axisTick: { show: false },
			axisLabel: { color: palette.muted, fontSize: 10, interval: 2 },
		},
		yAxis: {
			type: "category",
			data: days,
			inverse: true,
			position: rtl ? "right" : "left",
			axisLine: { show: false },
			axisTick: { show: false },
			axisLabel: { color: palette.muted, fontSize: 10 },
		},
		series: [{ type: "heatmap", data, itemStyle: cellStyle(palette) }],
	};
}

export function weekHourHeight(): number {
	return 7 * CELL + 56;
}

export function weekHourWidth(): number {
	return LABEL_WIDTH + 24 * CELL + 16;
}
