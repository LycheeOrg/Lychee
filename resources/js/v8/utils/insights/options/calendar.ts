/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { EChartsOption } from "echarts";
import type { InsightsPalette } from "@/v8/composables/insights/useInsightsTheme";
import { escapeHtml, formatDate, isoWeek, isoWeeksInYear, monthNames, weekdayNames } from "../format";
import { itemValue, tooltipStyle } from "./common";
import { cellStyle, LABEL_WIDTH, stepMap, type StepLegend } from "./squares";

/** Side of one calendar square, gap included (px). */
export const CELL = 14;

type CalendarLabels = StepLegend & {
	/** "<when>: <n> photos", built by the caller. */
	tooltip: (when: string, count: number) => string;
	week: (week: number) => string;
};

/** Colour steps at the configured per-day thresholds (FR-085-10); `scale` is 7 for week cells. */
function thresholdMap(data: App.Http.Resources.Insights.CalendarData, palette: InsightsPalette, labels: CalendarLabels, scale: number) {
	return stepMap(palette, [data.low * scale, data.medium * scale, data.high * scale], labels, CELL);
}

function sundayFirst(mondayFirst: string[]): string[] {
	return [mondayFirst[6], ...mondayFirst.slice(0, 6)];
}

/** GitHub-style day grid of one year: weeks as columns, Monday … Sunday as rows. */
export function dayGridOption(
	data: App.Http.Resources.Insights.CalendarData,
	year: number,
	palette: InsightsPalette,
	labels: CalendarLabels,
): EChartsOption {
	const prefix = `${year}-`;

	return {
		tooltip: {
			...tooltipStyle(palette),
			formatter: (params) => {
				const [date, count] = itemValue<[string, number]>(params);
				return escapeHtml(labels.tooltip(formatDate(date), count));
			},
		},
		visualMap: thresholdMap(data, palette, labels, 1),
		calendar: {
			range: String(year),
			top: 24,
			left: LABEL_WIDTH,
			cellSize: CELL,
			// nameMap is indexed Sunday first, whatever firstDay is.
			dayLabel: { firstDay: 1, nameMap: sundayFirst(weekdayNames()), color: palette.muted, fontSize: 10 },
			monthLabel: { nameMap: monthNames(), color: palette.muted, fontSize: 10 },
			yearLabel: { show: false },
			splitLine: { show: false },
			itemStyle: { color: palette.surface, ...cellStyle(palette) },
		},
		series: [
			{
				type: "heatmap",
				coordinateSystem: "calendar",
				itemStyle: cellStyle(palette),
				data: data.dates.flatMap((date, i) => (date.startsWith(prefix) ? [[date, data.counts[i]]] : [])),
			},
		],
	};
}

/** Monday `Y-m-d` of the week of a date. */
function mondayOf(date: string): string {
	const [y, m, d] = date.split("-").map(Number);
	const day = new Date(Date.UTC(y, m - 1, d));
	day.setUTCDate(day.getUTCDate() - ((day.getUTCDay() + 6) % 7));
	return day.toISOString().slice(0, 10);
}

/** ISO years from the first to the last photo, oldest first. */
function yearRows(data: App.Http.Resources.Insights.CalendarData): number[] {
	if (data.dates.length === 0) {
		return [];
	}
	const first = isoWeek(mondayOf(data.dates[0])).year;
	const last = isoWeek(mondayOf(data.dates[data.dates.length - 1])).year;
	return Array.from({ length: last - first + 1 }, (_, i) => first + i);
}

/** One row per year, one rounded square per ISO week, every week drawn. */
export function weekGridOption(
	data: App.Http.Resources.Insights.CalendarData,
	palette: InsightsPalette,
	labels: CalendarLabels,
	rtl: boolean,
): EChartsOption {
	const weekCounts = new Map<string, number>();
	data.dates.forEach((date, i) => {
		const { year, week } = isoWeek(mondayOf(date));
		weekCounts.set(`${year}-${week}`, (weekCounts.get(`${year}-${week}`) ?? 0) + data.counts[i]);
	});
	const years = yearRows(data);
	const cells = years.flatMap((year, row) =>
		Array.from({ length: isoWeeksInYear(year) }, (_, i) => [i, row, weekCounts.get(`${year}-${i + 1}`) ?? 0]),
	);

	return {
		grid: { left: rtl ? 8 : LABEL_WIDTH, right: rtl ? LABEL_WIDTH : 8, top: 4, width: 53 * CELL, height: years.length * CELL },
		tooltip: {
			...tooltipStyle(palette),
			formatter: (params) => {
				const [week, row, count] = itemValue<[number, number, number]>(params);
				return escapeHtml(labels.tooltip(`${labels.week(week + 1)}, ${years[row]}`, count));
			},
		},
		visualMap: thresholdMap(data, palette, labels, 7),
		xAxis: { type: "category", data: Array.from({ length: 53 }, (_, i) => String(i + 1)), inverse: rtl, show: false },
		yAxis: {
			type: "category",
			data: years.map(String),
			inverse: true,
			position: rtl ? "right" : "left",
			axisLine: { show: false },
			axisTick: { show: false },
			axisLabel: { color: palette.muted, fontSize: 10 },
		},
		series: [{ type: "heatmap", data: cells, itemStyle: cellStyle(palette) }],
	};
}

export function gridHeight(data: App.Http.Resources.Insights.CalendarData, isYear: boolean): number {
	return isYear ? 8 * CELL + 64 : yearRows(data).length * CELL + 44;
}

export function gridWidth(): number {
	return LABEL_WIDTH + 53 * CELL + 16;
}
