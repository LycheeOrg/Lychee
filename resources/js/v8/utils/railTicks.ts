/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { phpDateFormat } from "@/v8/utils/phpDateFormat";

/**
 * Feature 094 — scrubber adaptive ticks. Pure helpers that pick the
 * resting-state label unit of `TimelineDatesV3.vue` from the rail entries'
 * own date keys (FR-094-02): the coarsest calendar unit in which the data
 * varies, never finer than the entries themselves. Also the hourly lens
 * labels and lens width of `/timeline` (FR-094-08, FR-094-09). Label formats
 * are admin settings (Feature 095, FR-095-03).
 */

/** 1 = year, 2 = month, 3 = day, 4 = hour: the number of `-` segments of a date key. */
export type Precision = 1 | 2 | 3 | 4;

export type RailLabel = { unitKey: string; label: string; topPx: number };
export type RailDot = { topPx: number };
export type RailTicks = { unit: Precision | null; labels: RailLabel[]; dots: RailDot[] };

/** Rail entry as `TimelineDatesV3.vue` lays it out: an id and its content-space `top`. */
export type RailEntry = { bucketId: string; top: number };

/** PHP `date()` formats of the resting labels by unit: `date_scrubber_format_year` … `_hour` (FR-095-01). */
export type TickFormats = { year: string; month: string; day: string; hour: string };

const LABEL_EXCLUSION_PX = 16;
const DOT_SPACING_PX = 4;

const DATE_KEY = /^\d{4}(-\d{2}){0,3}$/;

/** The date key of a rail entry: its id without the `#n` run suffix, or `null` when not a date (FR-094-01). */
export function entryKeyOf(bucketId: string): string | null {
	const key = bucketId.split("#")[0];
	return DATE_KEY.test(key) ? key : null;
}

export function precisionOf(key: string): Precision {
	return key.split("-").length as Precision;
}

/** The first `unit` segments of a key: the id of the year / month / day / hour containing it. */
export function unitKeyOf(key: string, unit: Precision): string {
	return key.split("-").slice(0, unit).join("-");
}

/** Coarsest unit whose prefix differs between the smallest and largest key; `e` when they are equal. */
export function spanUnit(min: string, max: string, e: Precision): Precision {
	const a = min.split("-");
	const b = max.split("-");
	for (let i = 0; i < e; i++) {
		if (a[i] !== b[i]) {
			return (i + 1) as Precision;
		}
	}
	return e;
}

/**
 * Keys are capture-local wall-clock fields, so they are built as a UTC instant
 * and formatted at a fixed `+00:00` offset: built in the viewer's zone, an hour
 * skipped by its DST change (`2026-03-08-02` in America/New_York) would print
 * as `03:00`.
 */
const KEY_ZONE = "+00:00";

function dateOfKey(key: string): Date {
	const [year, month, day, hour] = key.split("-").map(Number);
	return new Date(Date.UTC(year, (month ?? 1) - 1, day ?? 1, hour ?? 0));
}

function formatKey(format: string, key: string): string {
	return phpDateFormat(format, dateOfKey(key), KEY_ZONE);
}

function formatOfUnit(formats: TickFormats, unit: Precision): string {
	return [formats.year, formats.month, formats.day, formats.hour][unit - 1];
}

/** Resting label of the `unit` containing `key` (FR-094-03). Keys are capture-local: no zone conversion. */
export function formatTickLabel(key: string, unit: Precision, formats: TickFormats): string {
	return formatKey(formatOfUnit(formats, unit), unitKeyOf(key, unit));
}

type KeyedEntry = { key: string; top: number };

function keyedEntries(entries: RailEntry[]): KeyedEntry[] {
	const out: KeyedEntry[] = [];
	for (const entry of entries) {
		const key = entryKeyOf(entry.bucketId);
		if (key !== null) {
			out.push({ key, top: entry.top });
		}
	}
	return out;
}

/** Label unit L = span of the entries (FR-094-02); `null` without a dated entry. */
export function labelUnitOf(keys: string[]): Precision | null {
	if (keys.length === 0) {
		return null;
	}
	let min = keys[0];
	let max = keys[0];
	for (const key of keys) {
		min = key < min ? key : min;
		max = key > max ? key : max;
	}
	return spanUnit(min, max, precisionOf(keys[0]));
}

/** Index of each entry that is the first of its `unit` in rail order (a unit recurring later starts nothing). */
function unitStarts(entries: KeyedEntry[], unit: Precision): boolean[] {
	const seen = new Set<string>();
	return entries.map((entry) => {
		const unitKey = unitKeyOf(entry.key, unit);
		const isStart = !seen.has(unitKey);
		seen.add(unitKey);
		return isStart;
	});
}

function cullLabels(candidates: RailLabel[]): RailLabel[] {
	const kept: RailLabel[] = [];
	for (const candidate of candidates) {
		const last = kept[kept.length - 1];
		if (last === undefined || Math.abs(candidate.topPx - last.topPx) >= LABEL_EXCLUSION_PX) {
			kept.push(candidate);
		}
	}
	return kept;
}

/** `/timeline` dots: the first entry of each unit one level finer than L, when the entries are that precise (FR-094-06). */
function unitDotTops(entries: KeyedEntry[], unit: Precision): number[] {
	const dotUnit = unit + 1;
	if (dotUnit > precisionOf(entries[0].key)) {
		return [];
	}
	const starts = unitStarts(entries, dotUnit as Precision);
	return entries.filter((_, i) => starts[i]).map((entry) => entry.top);
}

/**
 * Album dots: every section header, except the one directly above an entry
 * that starts an L-unit — that unit's label already marks it (FR-094-06).
 * Headers finer than the day runs fall inside a run and always draw a dot.
 */
function separatorDotTops(entries: KeyedEntry[], separatorTops: number[], unit: Precision): number[] {
	const starts = unitStarts(entries, unit);
	const out: number[] = [];
	let next = 0;
	for (let i = 0; i < separatorTops.length; i++) {
		const top = separatorTops[i];
		while (next < entries.length && entries[next].top < top) {
			next++;
		}
		const isDirectlyAbove = next < entries.length && (i + 1 === separatorTops.length || separatorTops[i + 1] >= entries[next].top);
		if (!(isDirectlyAbove && starts[next])) {
			out.push(top);
		}
	}
	return out;
}

/** Drops dots within 16 px of a kept label, then dots within 4 px of the previous kept dot. Both lists are in rail order. */
function cullDots(dotPx: number[], labels: RailLabel[]): RailDot[] {
	const kept: RailDot[] = [];
	let j = 0;
	for (const px of dotPx) {
		while (j < labels.length && labels[j].topPx < px - LABEL_EXCLUSION_PX) {
			j++;
		}
		const nearLabel = j < labels.length && labels[j].topPx < px + LABEL_EXCLUSION_PX;
		const last = kept[kept.length - 1];
		if (!nearLabel && (last === undefined || px - last.topPx >= DOT_SPACING_PX)) {
			kept.push({ topPx: px });
		}
	}
	return kept;
}

/**
 * Resting-state ticks of the rail (FR-094-02 … 06). `entries` are in rail
 * order with non-decreasing tops; `toRailPx` maps a content-space top to the
 * rail. `separatorTops` given → album dots, else `/timeline` dots.
 */
export function buildRailTicks(input: {
	entries: RailEntry[];
	separatorTops?: number[];
	toRailPx: (contentTop: number) => number;
	formats: TickFormats;
}): RailTicks {
	const entries = keyedEntries(input.entries);
	const unit = labelUnitOf(entries.map((entry) => entry.key));
	if (unit === null) {
		return { unit: null, labels: [], dots: [] };
	}
	const starts = unitStarts(entries, unit);
	const labels = cullLabels(
		entries
			.filter((_, i) => starts[i])
			.map((entry) => ({
				unitKey: unitKeyOf(entry.key, unit),
				label: formatTickLabel(entry.key, unit, input.formats),
				topPx: input.toRailPx(entry.top),
			})),
	);
	const dotTops = input.separatorTops === undefined ? unitDotTops(entries, unit) : separatorDotTops(entries, input.separatorTops, unit);
	return { unit, labels, dots: cullDots(dotTops.map(input.toRailPx), labels) };
}

/** `none`: ids are not hour-precise; `one_day`: every dated hour id is on the same day; `several_days` otherwise. */
export type HourlyMode = "none" | "one_day" | "several_days";

export function hourlyModeOf(bucketIds: string[]): HourlyMode {
	const keys = bucketIds.map(entryKeyOf).filter((key): key is string => key !== null);
	if (keys.length === 0 || precisionOf(keys[0]) !== 4) {
		return "none";
	}
	const day = unitKeyOf(keys[0], 3);
	return keys.every((key) => unitKeyOf(key, 3) === day) ? "one_day" : "several_days";
}

/** FR-094-09: the lens widens while it shows dated hour labels. */
export function isWideLens(bucketIds: string[]): boolean {
	return hourlyModeOf(bucketIds) === "several_days";
}

/** FR-094-11: hour-only rails (every photo on one day) open no lens; the focal line and pill suffice. */
export function hasLens(bucketIds: string[]): boolean {
	return hourlyModeOf(bucketIds) !== "one_day";
}

/**
 * FR-094-08: on hour-precise buckets, lens and pill labels become the day in
 * `dayFormat` plus the hour in `hourFormat` (FR-095-04), or the hour only
 * when every photo is on one day.
 * Other precisions are returned unchanged; undated ids keep their label.
 */
export function hourlyLensLabels(
	buckets: App.Http.Resources.V3.PhotoBucketResource,
	dayFormat: string,
	hourFormat: string,
): App.Http.Resources.V3.PhotoBucketResource {
	const mode = hourlyModeOf(buckets.bucket_ids);
	if (mode === "none") {
		return buckets;
	}
	const labels = buckets.bucket_ids.map((bucketId, i) => {
		const key = entryKeyOf(bucketId);
		if (key === null) {
			return buckets.labels[i];
		}
		const hour = formatKey(hourFormat, key);
		return mode === "one_day" ? hour : `${formatKey(dayFormat, key)} ${hour}`;
	});
	return { ...buckets, labels };
}
