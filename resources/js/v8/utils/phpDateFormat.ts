import { getActiveLanguage } from "laravel-vue-i18n";

/**
 * Reproduces PHP's `date()` format-character semantics client-side.
 * `date_format_album_thumb`/`date_format_hero_created_at`-style configs are
 * free-text (`type_range: STRING_REQ`, not an enum) — an admin can type any
 * valid PHP `date()` format string, so this must handle the general case,
 * not a fixed handful of tokens. Adapted from a community PHP-date-to-JS
 * reference implementation. Unrecognized characters pass through literally,
 * matching PHP's own `date()` behavior for an unknown format character.
 *
 * `\`-escapes the next character as a literal, exactly like PHP's `date()`.
 *
 * Deliberately plain TS, not Rust/WASM: this formats a handful of date
 * fields per render, so WASM's compute win doesn't apply and the call
 * boundary overhead would likely lose to plain JS here. It also leans on
 * `Intl.DateTimeFormat`/`navigator.language` for locale-aware names and
 * timezone abbreviations - Web APIs a WASM module can't call directly,
 * so it would still need a JS shim marshaling strings for every lookup,
 * negating the point of moving it out of TS.
 */

// Localized via the visitor's own browser locale, but only when Lychee's
// configured app language is English: a non-English install already has an
// admin-chosen language driving the rest of the UI, so day/month names stay
// the literal, un-localized English `date()` always produces rather than
// following a guest's possibly-unrelated browser language. `D`/`M` use a
// real `"short"` formatter rather than slicing the long name, since a
// 3-character slice isn't a valid abbreviation in most non-English locales.
function buildDayNames(locale: string, weekday: "long" | "short"): string[] {
	const formatter = new Intl.DateTimeFormat(locale, { weekday, timeZone: "UTC" });
	// 2023-01-01 (UTC) was a Sunday — walk 7 consecutive UTC days from there
	// for the Sunday-first ordering `date()`'s day-of-week numbering expects.
	return Array.from({ length: 7 }, (_, i) => formatter.format(new Date(Date.UTC(2023, 0, 1 + i))));
}

function buildMonthNames(locale: string, month: "long" | "short"): string[] {
	const formatter = new Intl.DateTimeFormat(locale, { month, timeZone: "UTC" });
	return Array.from({ length: 12 }, (_, i) => formatter.format(new Date(Date.UTC(2023, i, 1))));
}

// Computed lazily (on first call to `phpDateFormat`, not at module load) since
// `getActiveLanguage()` instantiates laravel-vue-i18n's shared instance on first
// touch: calling it here at module scope raced app-v8.ts's `app.use(i18nVue, ...)`
// install whenever this module was pulled in before that ran, silently reverting
// the active language to English mid-init.
let dateNames: { day: string[]; dayShort: string[]; month: string[]; monthShort: string[] } | null = null;

function getDateNames() {
	if (dateNames === null) {
		const isAppLanguageEnglish = getActiveLanguage().toLowerCase().startsWith("en");
		const localeForDateNames = isAppLanguageEnglish ? navigator.language : "en";
		dateNames = {
			day: buildDayNames(localeForDateNames, "long"),
			dayShort: buildDayNames(localeForDateNames, "short"),
			month: buildMonthNames(localeForDateNames, "long"),
			monthShort: buildMonthNames(localeForDateNames, "short"),
		};
	}
	return dateNames;
}

function pad(value: number, length: number = 2): string {
	return String(value).padStart(length, "0");
}

function ordinalSuffix(day: number): string {
	if (day >= 11 && day <= 13) {
		return "th";
	}
	switch (day % 10) {
		case 1:
			return "st";
		case 2:
			return "nd";
		case 3:
			return "rd";
		default:
			return "th";
	}
}

function isLeapYear(year: number): boolean {
	return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
}

// The calendar helpers below take a "wall clock": a `Date` whose UTC fields
// hold the formatted zone's local date and time (see `phpDateFormat()`).
function dayOfYear(wallClock: Date): number {
	const start = Date.UTC(wallClock.getUTCFullYear(), 0, 1);
	const current = Date.UTC(wallClock.getUTCFullYear(), wallClock.getUTCMonth(), wallClock.getUTCDate());
	return Math.floor((current - start) / 86400000);
}

// The Thursday of `wallClock`'s ISO-8601 week - both the week number and the
// week-numbering year (`W`/`o`) are defined relative to it.
function isoThursday(wallClock: Date): Date {
	const d = new Date(Date.UTC(wallClock.getUTCFullYear(), wallClock.getUTCMonth(), wallClock.getUTCDate()));
	const day = d.getUTCDay() === 0 ? 7 : d.getUTCDay();
	d.setUTCDate(d.getUTCDate() + 4 - day);
	return d;
}

// ISO-8601 week number.
function isoWeekNumber(wallClock: Date): number {
	const d = isoThursday(wallClock);
	const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
	return Math.ceil(((d.getTime() - yearStart.getTime()) / 86400000 + 1) / 7);
}

// ISO-8601 week-numbering year: differs from the calendar year for dates in
// the first/last days of January/December that belong to an adjacent week.
function isoWeekYear(wallClock: Date): number {
	return isoThursday(wallClock).getUTCFullYear();
}

function daysInMonth(year: number, month0: number): number {
	return new Date(Date.UTC(year, month0 + 1, 0)).getUTCDate();
}

function timezoneOffsetString(offsetMinutes: number): string {
	const sign = offsetMinutes >= 0 ? "+" : "-";
	const abs = Math.abs(offsetMinutes);
	return `${sign}${pad(Math.floor(abs / 60))}:${pad(abs % 60)}`;
}

// Heuristic used across JS date libraries: a timezone's "standard" (non-DST)
// offset is the smaller (more-west) of its January/July offsets, so any date
// whose own offset is larger than that is in daylight saving time.
function isDaylightSavingTime(offsetMinutes: number, januaryOffsetMinutes: number, julyOffsetMinutes: number): boolean {
	return offsetMinutes > Math.min(januaryOffsetMinutes, julyOffsetMinutes);
}

// `Intl`'s abbreviation for `timeZone` (the viewer's own when `undefined`) at
// this specific date (so it reflects e.g. "PST" vs "PDT" rather than a fixed name).
function timezoneAbbreviation(date: Date, timeZone: string | undefined): string {
	const part = new Intl.DateTimeFormat("en-US", { timeZoneName: "short", timeZone }).formatToParts(date).find((p) => p.type === "timeZoneName");
	return part?.value ?? "";
}

// Swatch Internet Time: the mean solar day split into 1000 ".beats", counted
// from midnight in Biel Mean Time (UTC+1), independent of the viewer's zone.
function swatchInternetTime(date: Date): string {
	const millisSinceBmtMidnight = (((date.getTime() + 3600000) % 86400000) + 86400000) % 86400000;
	return pad(Math.floor(millisSinceBmtMidnight / 86400), 3);
}

const LOCAL_TIMEZONE_IDENTIFIER = Intl.DateTimeFormat().resolvedOptions().timeZone;

/** The zone a date is formatted in: its UTC offset at that date plus what the zone tokens print. */
type Zone = {
	offsetMinutes: number;
	identifier: string;
	abbreviation: () => string;
	isDst: () => boolean;
};

function viewerZone(date: Date): Zone {
	const year = date.getFullYear();
	return {
		offsetMinutes: -date.getTimezoneOffset(),
		identifier: LOCAL_TIMEZONE_IDENTIFIER,
		abbreviation: () => timezoneAbbreviation(date, undefined),
		isDst: () =>
			isDaylightSavingTime(-date.getTimezoneOffset(), -new Date(year, 0, 1).getTimezoneOffset(), -new Date(year, 6, 1).getTimezoneOffset()),
	};
}

// PHP accepts a bare UTC offset (`+02:00`) as a timezone - it has no DST,
// prints itself for `e` and `GMT+0200` for `T`.
const FIXED_OFFSET_ZONE = /^([+-])(\d{2}):?(\d{2})$/;

function fixedOffsetZone(timeZone: string): Zone | null {
	const match = FIXED_OFFSET_ZONE.exec(timeZone);
	if (match === null) {
		return null;
	}
	const offsetMinutes = (match[1] === "-" ? -1 : 1) * (Number(match[2]) * 60 + Number(match[3]));
	return {
		offsetMinutes,
		identifier: timeZone,
		abbreviation: () => `GMT${timezoneOffsetString(offsetMinutes).replace(":", "")}`,
		isDst: () => false,
	};
}

// One formatter per zone - building an `Intl.DateTimeFormat` costs far more
// than formatting with it, and a listing formats thousands of tile dates.
const wallClockFormatters = new Map<string, Intl.DateTimeFormat | null>();

function wallClockFormatter(timeZone: string): Intl.DateTimeFormat | null {
	if (!wallClockFormatters.has(timeZone)) {
		wallClockFormatters.set(timeZone, createWallClockFormatter(timeZone));
	}
	return wallClockFormatters.get(timeZone) ?? null;
}

function createWallClockFormatter(timeZone: string): Intl.DateTimeFormat | null {
	try {
		return new Intl.DateTimeFormat("en-US", {
			timeZone,
			hourCycle: "h23",
			year: "numeric",
			month: "numeric",
			day: "numeric",
			hour: "numeric",
			minute: "numeric",
			second: "numeric",
		});
	} catch {
		// RangeError: a zone name this browser's `Intl` doesn't know.
		return null;
	}
}

// `timeZone`'s UTC offset at `date`, read back from its wall-clock fields.
function zoneOffsetMinutes(formatter: Intl.DateTimeFormat, date: Date): number {
	const fields: Record<string, number> = {};
	for (const part of formatter.formatToParts(date)) {
		fields[part.type] = Number(part.value);
	}
	const wallClockAsUtc = Date.UTC(fields.year, fields.month - 1, fields.day, fields.hour, fields.minute, fields.second);
	return Math.round((wallClockAsUtc - (date.getTime() - date.getUTCMilliseconds())) / 60000);
}

function namedZone(date: Date, timeZone: string): Zone | null {
	const formatter = wallClockFormatter(timeZone);
	if (formatter === null) {
		return null;
	}
	const offsetMinutes = zoneOffsetMinutes(formatter, date);
	const year = date.getUTCFullYear();
	return {
		offsetMinutes,
		identifier: timeZone,
		abbreviation: () => timezoneAbbreviation(date, timeZone),
		isDst: () =>
			isDaylightSavingTime(
				offsetMinutes,
				zoneOffsetMinutes(formatter, new Date(Date.UTC(year, 0, 1))),
				zoneOffsetMinutes(formatter, new Date(Date.UTC(year, 6, 1))),
			),
	};
}

// A zone neither PHP-offset-shaped nor known to `Intl` formats in the viewer's zone.
function resolveZone(date: Date, timeZone: string | null): Zone {
	if (timeZone === null) {
		return viewerZone(date);
	}
	return fixedOffsetZone(timeZone) ?? namedZone(date, timeZone) ?? viewerZone(date);
}

/**
 * @param format   PHP `date()`-style format string.
 * @param date     The date to format.
 * @param timeZone Zone to format in (IANA name or `±HH:MM` offset, as PHP
 *                 stores it - e.g. a photo's `taken_at_orig_tz`); `null`
 *                 formats in the viewer's own zone.
 */
export function phpDateFormat(format: string, date: Date, timeZone: string | null = null): string {
	const { day: DAY_NAMES, dayShort: DAY_NAMES_SHORT, month: MONTH_NAMES, monthShort: MONTH_NAMES_SHORT } = getDateNames();

	const zone = resolveZone(date, timeZone);
	const wallClock = new Date(date.getTime() + zone.offsetMinutes * 60000);
	const offset = timezoneOffsetString(zone.offsetMinutes);

	const year = wallClock.getUTCFullYear();
	const month0 = wallClock.getUTCMonth();
	const day = wallClock.getUTCDate();
	const weekday = wallClock.getUTCDay();
	const hours24 = wallClock.getUTCHours();
	const hours12 = hours24 % 12 === 0 ? 12 : hours24 % 12;
	const minutes = wallClock.getUTCMinutes();
	const seconds = wallClock.getUTCSeconds();
	const milliseconds = wallClock.getUTCMilliseconds();

	let result = "";
	for (let i = 0; i < format.length; i++) {
		const char = format[i];

		if (char === "\\" && i + 1 < format.length) {
			result += format[i + 1];
			i++;
			continue;
		}

		switch (char) {
			// Day
			case "d":
				result += pad(day);
				break;
			case "D":
				result += DAY_NAMES_SHORT[weekday];
				break;
			case "j":
				result += String(day);
				break;
			case "l":
				result += DAY_NAMES[weekday];
				break;
			case "N":
				result += String(weekday === 0 ? 7 : weekday);
				break;
			case "S":
				result += ordinalSuffix(day);
				break;
			case "w":
				result += String(weekday);
				break;
			case "z":
				result += String(dayOfYear(wallClock));
				break;
			// Week
			case "W":
				result += pad(isoWeekNumber(wallClock));
				break;
			// Month
			case "F":
				result += MONTH_NAMES[month0];
				break;
			case "m":
				result += pad(month0 + 1);
				break;
			case "M":
				result += MONTH_NAMES_SHORT[month0];
				break;
			case "n":
				result += String(month0 + 1);
				break;
			case "t":
				result += String(daysInMonth(year, month0));
				break;
			// Year
			case "L":
				result += isLeapYear(year) ? "1" : "0";
				break;
			case "o":
				result += String(isoWeekYear(wallClock));
				break;
			case "X":
				result += `${year < 0 ? "-" : "+"}${pad(Math.abs(year), 4)}`;
				break;
			case "x":
				result += year < 1 || year > 9999 ? `${year < 0 ? "-" : "+"}${pad(Math.abs(year), 4)}` : String(year);
				break;
			case "Y":
				result += String(year);
				break;
			case "y":
				result += pad(year % 100);
				break;
			// Time
			case "a":
				result += hours24 < 12 ? "am" : "pm";
				break;
			case "A":
				result += hours24 < 12 ? "AM" : "PM";
				break;
			case "B":
				result += swatchInternetTime(date);
				break;
			case "g":
				result += String(hours12);
				break;
			case "G":
				result += String(hours24);
				break;
			case "h":
				result += pad(hours12);
				break;
			case "H":
				result += pad(hours24);
				break;
			case "i":
				result += pad(minutes);
				break;
			case "s":
				result += pad(seconds);
				break;
			case "u":
				result += `${pad(milliseconds, 3)}000`;
				break;
			case "v":
				result += pad(milliseconds, 3);
				break;
			// Timezone
			case "e":
				result += zone.identifier;
				break;
			case "I":
				result += zone.isDst() ? "1" : "0";
				break;
			case "O":
				result += offset.replace(":", "");
				break;
			case "P":
				result += offset;
				break;
			case "p":
				result += zone.offsetMinutes === 0 ? "Z" : offset;
				break;
			case "T":
				result += zone.abbreviation();
				break;
			case "Z":
				result += String(zone.offsetMinutes * 60);
				break;
			// Full date/time
			case "U":
				result += String(Math.floor(date.getTime() / 1000));
				break;
			case "c":
				result += `${pad(year, 4)}-${pad(month0 + 1)}-${pad(day)}T${pad(hours24)}:${pad(minutes)}:${pad(seconds)}${offset}`;
				break;
			case "r":
				result += `${DAY_NAMES_SHORT[weekday]}, ${pad(day)} ${MONTH_NAMES_SHORT[month0]} ${year} ${pad(hours24)}:${pad(minutes)}:${pad(seconds)} ${offset.replace(":", "")}`;
				break;
			default:
				// Unrecognized character: pass through literally, matching
				// PHP's own date() behavior for an unknown format character.
				result += char;
		}
	}

	return result;
}

// An SQL datetime column value, optionally ISO-shaped (`T` separator, offset suffix).
const SERVER_DATETIME = /^(\d{4}-\d{2}-\d{2})(?:[T ](\d{2}:\d{2}:\d{2})(?:\.(\d+))?)?(Z|[+-]\d{2}(?::?\d{2})?)?$/;

/**
 * Parses a raw datetime the v3 endpoints return unformatted: an SQL column
 * value (`YYYY-MM-DD HH:MM:SS[.ffffff]`, always UTC - `UTCBasedTimes`) or an
 * ISO 8601 string carrying its own offset. `new Date()` can't take the SQL
 * form as-is: engines read an offset-less date-time as viewer-local time,
 * and Safari rejects the space separator outright.
 *
 * @return `null` for a `null` or unparseable value.
 */
export function parseServerDateTime(raw: string | null): Date | null {
	const match = raw === null ? null : SERVER_DATETIME.exec(raw.trim());
	if (match === null) {
		return null;
	}
	const [, day, time = "00:00:00", fraction = "", offset = "Z"] = match;
	const millis = fraction.padEnd(3, "0").slice(0, 3);
	const zone = offset === "Z" ? "Z" : `${offset.slice(0, 3)}:${offset.length > 3 ? offset.slice(-2) : "00"}`;
	const date = new Date(`${day}T${time}.${millis}${zone}`);
	return Number.isNaN(date.getTime()) ? null : date;
}

/**
 * Formats a raw server datetime (see `parseServerDateTime()`) with a PHP
 * `date()` format string; `""` when there is no (parseable) value.
 */
export function formatServerDateTime(raw: string | null, format: string, timeZone: string | null = null): string {
	const date = parseServerDateTime(raw);
	return date === null ? "" : phpDateFormat(format, date, timeZone);
}

/**
 * Mirrors `ThumbAlbumResource::formatMinMaxDate()`
 * (`app/Http/Resources/Models/ThumbAlbumResource.php:116-132`) exactly,
 * executed client-side instead of re-fetched.
 *
 * `formatMinMaxDate()`'s actual branching, reproduced precisely:
 * - either `min_taken_at`/`max_taken_at` missing (including *exactly one*
 *   present — that is **not** a single-value collapse case) → returns
 *   `null`, the same early-return PHP takes for both "neither present" and
 *   "exactly one present".
 * - both present and equal → single value.
 * - both present and different → ordered join per `dateOrder`.
 *
 * @param minTakenAt Raw server datetime (`parseServerDateTime()`) or `null`.
 * @param maxTakenAt Raw server datetime (`parseServerDateTime()`) or `null`.
 * @param format     PHP `date()`-style format string (`date_format_album_thumb`).
 * @param dateOrder  `App.Enum.DateOrderingType` ("older_younger" | "younger_older").
 */
export function formatMinMaxDate(
	minTakenAt: string | null,
	maxTakenAt: string | null,
	format: string,
	dateOrder: App.Enum.DateOrderingType,
): string | null {
	const minDate = parseServerDateTime(minTakenAt);
	const maxDate = parseServerDateTime(maxTakenAt);
	if (minDate === null || maxDate === null) {
		return null;
	}

	if (maxTakenAt === minTakenAt) {
		return phpDateFormat(format, maxDate);
	}

	const minFormatted = phpDateFormat(format, minDate);
	const maxFormatted = phpDateFormat(format, maxDate);

	return dateOrder === "younger_older" ? `${maxFormatted} - ${minFormatted}` : `${minFormatted} - ${maxFormatted}`;
}
