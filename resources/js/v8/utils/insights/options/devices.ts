/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

export type DeviceMetric = "all" | "photos" | "videos" | "highlighted" | "located" | "with_people";
export type DeviceCategoryFilter = App.Enum.DeviceCategory | "all";

export type DeviceBar = { name: string | null; count: number; is_other: boolean };

const TOP = 10;

/**
 * Entries of one category (or all, merging same names across categories),
 * counted by `metric`: the ten largest, the rest summed as "other"
 * (FR-085-12).
 */
export function deviceBars(
	entries: App.Http.Resources.Insights.DeviceEntryData[],
	metric: DeviceMetric,
	category: DeviceCategoryFilter,
): DeviceBar[] {
	const totals = new Map<string | null, number>();
	for (const entry of entries) {
		if (category !== "all" && entry.category !== category) {
			continue;
		}
		totals.set(entry.name, (totals.get(entry.name) ?? 0) + entry[metric]);
	}

	const sorted = [...totals.entries()]
		.filter(([, count]) => count > 0)
		.sort((a, b) => b[1] - a[1] || Number(a[0] === null) - Number(b[0] === null) || (a[0] ?? "").localeCompare(b[0] ?? ""));
	const bars: DeviceBar[] = sorted.slice(0, TOP).map(([name, count]) => ({ name, count, is_other: false }));
	const rest = sorted.slice(TOP).reduce((sum, [, count]) => sum + count, 0);
	if (rest > 0) {
		bars.push({ name: null, count: rest, is_other: true });
	}

	return bars;
}
