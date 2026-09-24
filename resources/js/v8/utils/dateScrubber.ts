import { phpDateFormat } from "@/v8/utils/phpDateFormat";
import type { VirtualAlbumRow } from "@/v8/composables/album/virtualAlbumRows";

/**
 * Feature 071 — album date scrubber. Pure helpers that turn an album grid's
 * already-laid-out tiles into the day-level entries `TimelineDatesV3.vue`
 * consumes (ADR-0011: derived client-side from per-tile dates, bucket
 * storage untouched), plus the rail's eligibility decision (FR-071-06).
 */

export type DateScrubberSource = "photos" | "albums";

/** `AlbumConfig.photo_date_scrubber_field` / `album_date_scrubber_field` values. */
export type DateScrubberField = "taken_at" | "created_at" | "min_taken_at" | "max_taken_at" | "title";

export type DateScrubEntry = { bucketId: string; label: string; count: number; top: number; height: number };

export type DateScrubLayout = { entries: DateScrubEntry[]; totalHeight: number };

/** The date-bearing fields a tile may be read from — photo and album tiles each supply the subset they have. */
export type DatedTile = {
	title: string;
	created_at: string | null;
	taken_at?: string | null;
	min_taken_at?: string | null;
	max_taken_at?: string | null;
};

const RAW_DAY = /^\d{4}-\d{2}-\d{2}/;

/** Same leading-date grammar as `TimelineData::parseDateFromTitle()` — missing month/day default to `01`. */
const TITLE_DATE_PREFIX = /^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?/;

/** `YYYY-MM-DD` from a raw DB datetime — the same prefix the server's bucket truncation reads, so rail days agree with header buckets. */
export function dayKeyOf(raw: string | null | undefined): string | null {
	if (raw === null || raw === undefined || !RAW_DAY.test(raw)) {
		return null;
	}
	return raw.slice(0, 10);
}

export function parseTitleDatePrefix(title: string): string | null {
	const match = TITLE_DATE_PREFIX.exec(title.trim());
	if (match === null) {
		return null;
	}
	return `${match[1]}-${match[2] ?? "01"}-${match[3] ?? "01"}`;
}

export function tileDayKey(tile: DatedTile, field: DateScrubberField): string | null {
	if (field === "title") {
		return parseTitleDatePrefix(tile.title);
	}
	return dayKeyOf(tile[field]);
}

export function formatDayLabel(day: string, format: string): string {
	const [year, month, date] = day.split("-").map(Number);
	return phpDateFormat(format, new Date(year, month - 1, date));
}

/**
 * Groups tiles (in grid order, each with its laid-out `top`) into
 * consecutive same-day runs. Undated tiles join no run and do not break one.
 * A day recurring in a later run gets a `#n` suffix so ids stay unique —
 * `TimelineDatesV3.vue` only reads the year/month segments, which are
 * unaffected. Tops are forced non-decreasing (masonry columns interleave)
 * because the rail binary-searches them.
 */
export function deriveDayScrubEntries(
	items: { day: string | null; top: number }[],
	totalHeight: number,
	formatLabel: (day: string) => string,
): DateScrubLayout {
	const runs: { bucketId: string; day: string; top: number; count: number }[] = [];
	const runsPerDay = new Map<string, number>();

	for (const item of items) {
		if (item.day === null) {
			continue;
		}
		const last = runs[runs.length - 1];
		if (last !== undefined && last.day === item.day) {
			last.count++;
			last.top = Math.min(last.top, item.top);
			continue;
		}
		const seen = runsPerDay.get(item.day) ?? 0;
		runsPerDay.set(item.day, seen + 1);
		runs.push({ bucketId: seen === 0 ? item.day : `${item.day}#${seen}`, day: item.day, top: item.top, count: 1 });
	}

	for (let i = 1; i < runs.length; i++) {
		runs[i].top = Math.max(runs[i].top, runs[i - 1].top);
	}

	const entries = runs.map((run, i) => {
		const end = i + 1 < runs.length ? runs[i + 1].top : totalHeight;
		return { bucketId: run.bucketId, label: formatLabel(run.day), count: run.count, top: run.top, height: Math.max(0, end - run.top) };
	});
	return { entries, totalHeight };
}

/** The bucket-tier shape `TimelineDatesV3.vue`'s `buckets` prop expects, synthesized from day entries. */
export function toRailBuckets(entries: DateScrubEntry[]): App.Http.Resources.V3.PhotoBucketResource {
	return {
		bucket_ids: entries.map((e) => e.bucketId),
		labels: entries.map((e) => e.label),
		counts: entries.map((e) => e.count),
		bucketable: true,
	};
}

function singleKind(albumCount: number, photoCount: number): DateScrubberSource | null {
	if (albumCount > 0 && photoCount === 0) {
		return "albums";
	}
	if (photoCount > 0 && albumCount === 0) {
		return "photos";
	}
	return null;
}

/**
 * FR-071-06: which grid (if any) the rail follows. A count is `undefined`
 * until its listing has resolved.
 */
export function resolveDateScrubberSource(input: {
	soaActive: boolean;
	enabled: boolean;
	albumCount: number | undefined;
	photoCount: number | undefined;
	photoField: string | null;
	albumField: string | null;
}): DateScrubberSource | null {
	if (!input.soaActive || !input.enabled || input.albumCount === undefined || input.photoCount === undefined) {
		return null;
	}
	const kind = singleKind(input.albumCount, input.photoCount);
	const field = kind === "photos" ? input.photoField : input.albumField;
	return kind !== null && field !== null ? kind : null;
}

/**
 * Album grid/list rows → per-tile `{day, top}` items plus the rows' total
 * height. Every tile in a row shares the row's top.
 */
export function albumRowScrubItems(
	rows: VirtualAlbumRow[],
	rowHeights: number[],
	tiles: DatedTile[],
	field: DateScrubberField,
): { items: { day: string | null; top: number }[]; totalHeight: number } {
	const items: { day: string | null; top: number }[] = [];
	let top = 0;
	for (let i = 0; i < rows.length; i++) {
		const row = rows[i];
		if (row.type === "tiles") {
			for (const tile of tiles.slice(row.startIndex, row.startIndex + row.count)) {
				items.push({ day: tileDayKey(tile, field), top });
			}
		}
		top += rowHeights[i] ?? 0;
	}
	return { items, totalHeight: top };
}
