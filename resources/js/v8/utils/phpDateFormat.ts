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

const isAppLanguageEnglish = getActiveLanguage().toLowerCase().startsWith("en");
const localeForDateNames = isAppLanguageEnglish ? navigator.language : "en";

const DAY_NAMES = buildDayNames(localeForDateNames, "long");
const DAY_NAMES_SHORT = buildDayNames(localeForDateNames, "short");
const MONTH_NAMES = buildMonthNames(localeForDateNames, "long");
const MONTH_NAMES_SHORT = buildMonthNames(localeForDateNames, "short");

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

function dayOfYear(date: Date): number {
	const start = new Date(date.getFullYear(), 0, 1);
	return Math.floor((date.getTime() - start.getTime()) / 86400000);
}

// ISO-8601 week number.
function isoWeekNumber(date: Date): number {
	const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
	const day = d.getUTCDay() === 0 ? 7 : d.getUTCDay();
	d.setUTCDate(d.getUTCDate() + 4 - day);
	const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
	return Math.ceil(((d.getTime() - yearStart.getTime()) / 86400000 + 1) / 7);
}

function daysInMonth(year: number, month0: number): number {
	return new Date(year, month0 + 1, 0).getDate();
}

function timezoneOffsetString(date: Date): string {
	const offsetMinutes = -date.getTimezoneOffset();
	const sign = offsetMinutes >= 0 ? "+" : "-";
	const abs = Math.abs(offsetMinutes);
	return `${sign}${pad(Math.floor(abs / 60))}:${pad(abs % 60)}`;
}

/**
 * @param format PHP `date()`-style format string.
 * @param date   The date to format.
 */
export function phpDateFormat(format: string, date: Date): string {
	const year = date.getFullYear();
	const month0 = date.getMonth();
	const day = date.getDate();
	const weekday = date.getDay();
	const hours24 = date.getHours();
	const hours12 = hours24 % 12 === 0 ? 12 : hours24 % 12;
	const minutes = date.getMinutes();
	const seconds = date.getSeconds();

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
				result += String(dayOfYear(date));
				break;
			// Week
			case "W":
				result += pad(isoWeekNumber(date));
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
			case "v":
				result += String(date.getMilliseconds()).padStart(3, "0");
				break;
			// Full date/time
			case "U":
				result += String(Math.floor(date.getTime() / 1000));
				break;
			case "c":
				result += `${pad(year, 4)}-${pad(month0 + 1)}-${pad(day)}T${pad(hours24)}:${pad(minutes)}:${pad(seconds)}${timezoneOffsetString(date)}`;
				break;
			case "r":
				result += `${DAY_NAMES_SHORT[weekday]}, ${pad(day)} ${MONTH_NAMES_SHORT[month0]} ${year} ${pad(hours24)}:${pad(minutes)}:${pad(seconds)} ${timezoneOffsetString(date).replace(":", "")}`;
				break;
			default:
				// Unrecognized character: pass through literally, matching
				// PHP's own date() behavior for an unknown format character.
				result += char;
		}
	}

	return result;
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
 * @param minTakenAt ISO date string or `null`.
 * @param maxTakenAt ISO date string or `null`.
 * @param format     PHP `date()`-style format string (`date_format_album_thumb`).
 * @param dateOrder  `App.Enum.DateOrderingType` ("older_younger" | "younger_older").
 */
export function formatMinMaxDate(
	minTakenAt: string | null,
	maxTakenAt: string | null,
	format: string,
	dateOrder: App.Enum.DateOrderingType,
): string | null {
	if (minTakenAt === null || maxTakenAt === null) {
		return null;
	}

	if (maxTakenAt === minTakenAt) {
		return phpDateFormat(format, new Date(maxTakenAt));
	}

	const minFormatted = phpDateFormat(format, new Date(minTakenAt));
	const maxFormatted = phpDateFormat(format, new Date(maxTakenAt));

	return dateOrder === "younger_older" ? `${maxFormatted} - ${minFormatted}` : `${minFormatted} - ${maxFormatted}`;
}
