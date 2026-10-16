import { phpDateFormat } from "@/v8/utils/phpDateFormat";

/**
 * Feature 094 — scrubber adaptive ticks. Pure helpers that pick the
 * resting-state label unit of `TimelineDatesV3.vue` from the rail entries'
 * own date keys (FR-094-02): the coarsest calendar unit in which the data
 * varies, never finer than the entries themselves. Also the hourly lens
 * labels and lens width of `/timeline` (FR-094-08, FR-094-09).
 */

/** 1 = year, 2 = month, 3 = day, 4 = hour: the number of `-` segments of a date key. */
export type Precision = 1 | 2 | 3 | 4;

export type RailLabel = { unitKey: string; label: string; topPx: number };
export type RailDot = { topPx: number };
export type RailTicks = { unit: Precision | null; labels: RailLabel[]; dots: RailDot[] };

/** Rail entry as `TimelineDatesV3.vue` lays it out: an id and its content-space `top`. */
export type RailEntry = { bucketId: string; top: number };

/** Day and hour tick formats — fixed until made configurable (N-094-01). */
export const DAY_TICK_FORMAT = "d/m";
export const HOUR_TICK_FORMAT = "H:00";

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

function dateOfKey(key: string): Date {
	const [year, month, day, hour] = key.split("-").map(Number);
	return new Date(year, (month ?? 1) - 1, day ?? 1, hour ?? 0);
}

const TICK_FORMATS: Record<Precision, string> = { 1: "Y", 2: "M", 3: DAY_TICK_FORMAT, 4: HOUR_TICK_FORMAT };

/** Resting label of the `unit` containing `key` (FR-094-03). Keys are capture-local: no zone conversion. */
export function formatTickLabel(key: string, unit: Precision): string {
	return phpDateFormat(TICK_FORMATS[unit], dateOfKey(unitKeyOf(key, unit)));
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
export function buildRailTicks(input: { entries: RailEntry[]; separatorTops?: number[]; toRailPx: (contentTop: number) => number }): RailTicks {
	const entries = keyedEntries(input.entries);
	const unit = labelUnitOf(entries.map((entry) => entry.key));
	if (unit === null) {
		return { unit: null, labels: [], dots: [] };
	}
	const starts = unitStarts(entries, unit);
	const labels = cullLabels(
		entries
			.filter((_, i) => starts[i])
			.map((entry) => ({ unitKey: unitKeyOf(entry.key, unit), label: formatTickLabel(entry.key, unit), topPx: input.toRailPx(entry.top) })),
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

/**
 * FR-094-08: on hour-precise buckets, lens and pill labels become the day in
 * `dayFormat` plus the hour, or the hour only when every photo is on one day.
 * Other precisions are returned unchanged; undated ids keep their label.
 */
export function hourlyLensLabels(buckets: App.Http.Resources.V3.PhotoBucketResource, dayFormat: string): App.Http.Resources.V3.PhotoBucketResource {
	const mode = hourlyModeOf(buckets.bucket_ids);
	if (mode === "none") {
		return buckets;
	}
	const labels = buckets.bucket_ids.map((bucketId, i) => {
		const key = entryKeyOf(bucketId);
		if (key === null) {
			return buckets.labels[i];
		}
		const hour = phpDateFormat(HOUR_TICK_FORMAT, dateOfKey(key));
		return mode === "one_day" ? hour : `${phpDateFormat(dayFormat, dateOfKey(key))} ${hour}`;
	});
	return { ...buckets, labels };
}
