/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Formatting helpers for the Insights page (Feature 085).
 */

export function formatNumber(value: number, fractionDigits = 0): string {
	return value.toLocaleString(undefined, { maximumFractionDigits: fractionDigits });
}

export function formatPercent(share: number): string {
	// Small shares keep one decimal so that they do not read as 0 %.
	return share.toLocaleString(undefined, { style: "percent", maximumFractionDigits: share > 0 && share < 0.1 ? 1 : 0 });
}

/** Exposure time in seconds → `1/250 s` or `2 s`. */
export function formatShutter(seconds: number): string {
	if (seconds < 1) {
		return `1/${Math.round(1 / seconds)} s`;
	}
	return `${formatNumber(seconds, 1)} s`;
}

export function formatAperture(value: number): string {
	return `f/${formatNumber(value, 1)}`;
}

export function formatFocal(value: number): string {
	return `${formatNumber(value, 1)} mm`;
}

/** Seconds → `1:05:09` or `5:09`. */
export function formatDuration(seconds: number): string {
	const total = Math.round(seconds);
	const h = Math.floor(total / 3600);
	const m = Math.floor((total % 3600) / 60);
	const s = total % 60;
	const mm = h > 0 ? String(m).padStart(2, "0") : String(m);
	return (h > 0 ? `${h}:` : "") + `${mm}:${String(s).padStart(2, "0")}`;
}

/** Escapes text placed in ECharts HTML tooltips (EXIF strings come from files). */
export function escapeHtml(value: string): string {
	return value.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

/** Short weekday names, Monday first. */
export function weekdayNames(): string[] {
	const formatter = new Intl.DateTimeFormat(undefined, { weekday: "short", timeZone: "UTC" });
	// 2024-01-01 is a Monday.
	return Array.from({ length: 7 }, (_, i) => formatter.format(Date.UTC(2024, 0, 1 + i)));
}

/** Short month names, January first. */
export function monthNames(): string[] {
	const formatter = new Intl.DateTimeFormat(undefined, { month: "short", timeZone: "UTC" });
	return Array.from({ length: 12 }, (_, i) => formatter.format(Date.UTC(2024, i, 1)));
}

/** Local date `Y-m-d` → localised date. */
export function formatDate(date: string): string {
	const [y, m, d] = date.split("-").map(Number);
	return new Intl.DateTimeFormat(undefined, { dateStyle: "medium", timeZone: "UTC" }).format(Date.UTC(y, m - 1, d));
}

/**
 * ISO year and week of the Monday `Y-m-d` starting a week: the week belongs to
 * the year of its Thursday.
 */
export function isoWeek(monday: string): { year: number; week: number } {
	const [y, m, d] = monday.split("-").map(Number);
	const thursday = new Date(Date.UTC(y, m - 1, d + 3));
	const year = thursday.getUTCFullYear();
	const week = 1 + Math.floor((thursday.getTime() - Date.UTC(year, 0, 1)) / (7 * 86400000));
	return { year, week };
}

/** Number of ISO weeks of a year (52 or 53): 28 December is always in the last one. */
export function isoWeeksInYear(year: number): number {
	const december28 = new Date(Date.UTC(year, 11, 28));
	const monday = new Date(december28.getTime() - ((december28.getUTCDay() + 6) % 7) * 86400000);
	return isoWeek(monday.toISOString().slice(0, 10)).week;
}
