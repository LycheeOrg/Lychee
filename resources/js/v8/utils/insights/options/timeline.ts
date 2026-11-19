/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { EChartsOption } from "echarts";
import { trans } from "laravel-vue-i18n";
import type { InsightsPalette } from "@/v8/composables/insights/useInsightsTheme";
import { escapeHtml, formatAperture, formatDate, formatDuration, formatFocal, formatNumber, formatShutter } from "../format";
import { itemValue, tooltipStyle } from "./common";

type Event = App.Http.Resources.Insights.TimelineEventData;

export const CATEGORIES: App.Enum.TimelineCategory[] = ["first_last", "device", "milestone", "record", "break"];

/** One symbol per category: the timeline keeps the single accent hue (DESIGN.md). */
const SYMBOLS: Record<App.Enum.TimelineCategory, string> = {
	first_last: "circle",
	device: "diamond",
	milestone: "triangle",
	record: "rect",
	break: "pin",
};

export type EventTone = "first" | "last" | "milestone" | "other";

/** Firsts are green, lasts red, milestones yellow; the rest keeps the accent colour. */
export function eventTone(kind: App.Enum.TimelineEventKind): EventTone {
	if (kind === "milestone") {
		return "milestone";
	}
	if (kind.startsWith("first") || kind === "device_first") {
		return "first";
	}
	return kind.startsWith("last") || kind === "device_last" ? "last" : "other";
}

function toneColour(tone: EventTone, palette: InsightsPalette): string {
	return { first: palette.success, last: palette.error, milestone: palette.warning, other: palette.primary }[tone];
}

export function categorySymbol(category: App.Enum.TimelineCategory): string {
	return SYMBOLS[category];
}

/** Human text of an event (FR-085-19); `size` formats bytes. */
export function eventText(event: Event, size: (bytes: number) => string): string {
	const value = event.value ?? 0;
	const formatted: Partial<Record<App.Enum.TimelineEventKind, string>> = {
		milestone: formatNumber(value),
		longest_video: formatDuration(value),
		largest_file: size(value),
		highest_iso: formatNumber(value),
		longest_exposure: formatShutter(value),
		widest_aperture: formatAperture(value),
		longest_focal: formatFocal(value),
	};

	return trans(`insights.timeline.events.${event.kind}`, {
		device: event.subject ?? "",
		count: formatted[event.kind] ?? "",
		value: formatted[event.kind] ?? "",
	});
}

const LANES = 6;
/** Row of the events whose label found no free lane. */
const BASELINE = 1.25;
/** Share of the time span a label needs before the next one fits on the same lane. */
const LABEL_SPAN = 0.14;

/**
 * Lane of each event's label: the first lane whose previous label ends far
 * enough before it, or null when every lane is taken.
 */
function lanes(events: Event[]): (number | null)[] {
	const days = events.map((event) => Date.parse(event.date) / 86400000);
	const span = Math.max(1, Math.max(...days) - Math.min(...days));
	const free = Array.from({ length: LANES }, () => -Infinity);

	return days.map((day) => {
		const lane = free.findIndex((until) => until <= day);
		if (lane === -1) {
			return null;
		}
		free[lane] = day + span * LABEL_SPAN;
		return lane;
	});
}

/** Photos per month from the per-day calendar, every month between the first and the last. */
function monthlyDensity(calendar: App.Http.Resources.Insights.CalendarData): [string, number][] {
	const months = new Map<string, number>();
	calendar.dates.forEach((date, i) => months.set(date.slice(0, 7), (months.get(date.slice(0, 7)) ?? 0) + calendar.counts[i]));
	if (calendar.dates.length === 0) {
		return [];
	}

	const [firstYear, firstMonth] = calendar.dates[0].split("-").map(Number);
	const [lastYear, lastMonth] = calendar.dates[calendar.dates.length - 1].split("-").map(Number);
	const result: [string, number][] = [];
	for (let y = firstYear, m = firstMonth; y < lastYear || (y === lastYear && m <= lastMonth); m === 12 ? ((y += 1), (m = 1)) : (m += 1)) {
		const key = `${y}-${String(m).padStart(2, "0")}`;
		result.push([`${key}-15`, months.get(key) ?? 0]);
	}
	return result;
}

/**
 * Horizontal time axis: photos per month as an area, events as symbols on
 * four staggered lanes with labels hidden where they overlap; wheel zoom and
 * drag pan.
 */
export function timelineOption(
	events: Event[],
	calendar: App.Http.Resources.Insights.CalendarData,
	palette: InsightsPalette,
	size: (bytes: number) => string,
	rtl: boolean,
): EChartsOption {
	const density = monthlyDensity(calendar);
	const most = Math.max(1, ...density.map(([, count]) => count));

	return {
		grid: { left: 80, right: 80, top: 16, bottom: 56 },
		tooltip: {
			...tooltipStyle(palette),
			formatter: (params) => {
				const item = (Array.isArray(params) ? params[0] : params) as unknown as {
					seriesType?: string;
					data: { value: [string, number]; raw: number };
				};
				if (item.seriesType === "line") {
					return `${escapeHtml(item.data.value[0].slice(0, 7))}: <b>${formatNumber(item.data.raw)}</b>`;
				}
				const [date, , text] = itemValue<[string, number, string]>(params);
				return `${escapeHtml(formatDate(date))}<br/>${escapeHtml(text)}`;
			},
		},
		xAxis: {
			type: "time",
			inverse: rtl,
			axisLine: { lineStyle: { color: palette.border } },
			axisLabel: { color: palette.muted },
			splitLine: { show: false },
		},
		yAxis: { type: "value", min: 0, max: 5.2, show: false },
		dataZoom: [
			{ type: "inside", xAxisIndex: 0, filterMode: "weakFilter" },
			{
				type: "slider",
				xAxisIndex: 0,
				height: 18,
				bottom: 8,
				borderColor: palette.border,
				fillerColor: palette.steps[0],
				dataBackground: { lineStyle: { color: palette.border }, areaStyle: { color: palette.surface } },
				handleStyle: { color: palette.primary },
				textStyle: { color: palette.muted },
			},
		],
		series: [
			{
				type: "line",
				showSymbol: false,
				smooth: true,
				lineStyle: { width: 0 },
				areaStyle: { color: palette.steps[0] },
				// Scaled into the lower third of the chart; the tooltip shows the raw count.
				data: density.map(([month, count]) => ({ value: [month, (count / most) * 1.4], raw: count })),
			},
			{
				// One series for every event, so that overlapping labels are hidden across categories.
				type: "scatter",
				symbolSize: 12,
				itemStyle: { color: palette.primary, borderColor: palette.background, borderWidth: 1 },
				label: {
					show: true,
					position: "top",
					color: palette.text,
					fontSize: 10,
					width: 150,
					overflow: "truncate",
					formatter: (params: unknown) => itemValue<[string, number, string]>(params)[2],
				},
				// The symbol tells the category; labels without a free lane stay hidden (the list shows every event).
				data: lanes(events).map((lane, i) => ({
					// Unlabelled events sit on a baseline row, never on a labelled lane.
					value: [events[i].date, lane === null ? BASELINE : 1.7 + lane * 0.6, eventText(events[i], size)],
					symbol: SYMBOLS[events[i].category],
					itemStyle: { color: toneColour(eventTone(events[i].kind), palette) },
					label: { show: lane !== null },
				})),
			},
		],
	};
}
