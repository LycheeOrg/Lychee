/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { EChartsOption } from "echarts";
import type { InsightsPalette } from "@/v8/composables/insights/useInsightsTheme";
import { escapeHtml, formatNumber } from "../format";

/** Value of the hovered item; ECharts passes one item or a list of them. */
export function itemValue<T>(params: unknown): T {
	const item = Array.isArray(params) ? params[0] : params;
	return (item as { value: T }).value;
}

export function tooltipStyle(palette: InsightsPalette) {
	return {
		backgroundColor: palette.surface,
		borderColor: palette.border,
		textStyle: { color: palette.text },
	};
}

function axisStyle(palette: InsightsPalette) {
	return {
		axisLine: { lineStyle: { color: palette.border } },
		axisTick: { lineStyle: { color: palette.border } },
		axisLabel: { color: palette.muted },
		splitLine: { lineStyle: { color: palette.border, opacity: 0.5 } },
	};
}

export type BarOptions = {
	labels: string[];
	values: number[];
	palette: InsightsPalette;
	rtl: boolean;
	/** Bars grow sideways with labels on the start side (rankings). */
	horizontal?: boolean;
	log?: boolean;
};

/**
 * One bar series. Vertical bars run in reading direction; horizontal bars list
 * the first label on top.
 */
export function barOption({ labels, values, palette, rtl, horizontal = false, log = false }: BarOptions): EChartsOption {
	const categoryAxis = {
		type: "category" as const,
		data: labels,
		...axisStyle(palette),
		axisLabel: { color: palette.muted, width: 160, overflow: "truncate" as const },
		inverse: horizontal ? true : rtl,
		position: horizontal ? ((rtl ? "right" : "left") as "right" | "left") : undefined,
	};
	const valueAxis = {
		type: log ? ("log" as const) : ("value" as const),
		min: log ? 1 : 0,
		minInterval: 1,
		...axisStyle(palette),
		inverse: horizontal ? rtl : false,
		position: horizontal ? undefined : ((rtl ? "right" : "left") as "right" | "left"),
	};

	return {
		grid: { left: 8, right: 8, top: 8, bottom: 8, containLabel: true },
		tooltip: {
			trigger: "axis",
			axisPointer: { type: "shadow" },
			...tooltipStyle(palette),
			formatter: (params) => {
				const item = (Array.isArray(params) ? params[0] : params) as { name: string; value: number };
				return `${escapeHtml(item.name)}: <b>${formatNumber(item.value)}</b>`;
			},
		},
		xAxis: horizontal ? valueAxis : categoryAxis,
		yAxis: horizontal ? categoryAxis : valueAxis,
		series: [{ type: "bar", data: values, itemStyle: { color: palette.primary, borderRadius: 2 }, barMaxWidth: 32 }],
	};
}
