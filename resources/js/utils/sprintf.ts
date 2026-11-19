/**
 * Drop-in replacement for the `sprintf-js` package (https://github.com/alexei/sprintf.js).
 *
 * Same format syntax, same output, same `[sprintf]` errors — except that:
 *  - precision is clamped to what `toFixed` / `toExponential` / `toPrecision` accept, instead of
 *    throwing a RangeError (CVE-2026-97058, GHSA-hp3w-g68c-fv3c);
 *  - field width is clamped to MAX_WIDTH, so a short format string cannot allocate unbounded padding;
 *  - the cache of parsed format strings is bounded;
 *  - where sprintf-js crashes inside a built-in ("%.2g" of a string, "%.2v" of a number, "%v" of null),
 *    we return the obvious string instead.
 */

/** Largest precision accepted by Number.prototype.toFixed, toExponential and toPrecision. */
const MAX_PRECISION = 100;
/** Largest honoured field width; wider requests are clamped. */
const MAX_WIDTH = 1024;
/** Number of parsed format strings kept in the cache. */
const CACHE_SIZE = 1024;

// Conversion specifiers, as char codes.
const BINARY = 98; // b
const CHAR = 99; // c
const DECIMAL = 100; // d
const EXPONENT = 101; // e
const FIXED = 102; // f
const GENERAL = 103; // g
const INTEGER = 105; // i
const JSON_ = 106; // j
const OCTAL = 111; // o
const STRING = 115; // s
const BOOLEAN = 116; // t
const TYPE = 84; // T
const UNSIGNED = 117; // u
const VALUE = 118; // v
const HEX = 120; // x
const HEX_UPPER = 88; // X
const CONVERSIONS = "bcdefgijostTuvxX";

const PERCENT = 37; // %
const DOLLAR = 36; // $
const QUOTE = 39; // '
const OPEN_PAREN = 40; // (
const PLUS = 43; // +
const MINUS = 45; // -
const DOT = 46; // .
const ZERO = 48; // 0

const KEY = /^([a-z_][a-z_\d]*)/i;
const KEY_ACCESS = /^\.([a-z_][a-z_\d]*)/i;
const INDEX_ACCESS = /^\[(\d+)\]/;

interface Placeholder {
	/** Conversion specifier, as a char code. */
	type: number;
	/** Explicit argument index (`%2$s` → 1), or -1 to take the next argument. */
	index: number;
	/** Property path of a named argument (`%(user.name)s` → ["user", "name"]). */
	keys: string[] | null;
	plus: boolean;
	left: boolean;
	padChar: string;
	/** 0 when absent. */
	width: number;
	/** -1 when absent. Not clamped: `%.Ns` truncates strings to any length. */
	precision: number;
}

interface Template {
	/** Text around the placeholders: literals.length === placeholders.length + 1. */
	literals: string[];
	placeholders: Placeholder[];
}

export function sprintf(format: string, ...args: unknown[]): string {
	return render(compile(format), args);
}

export function vsprintf(format: string, args?: readonly unknown[] | Record<string, unknown> | null): string {
	return render(compile(format), args ? (Array.isArray(args) ? args : [args]) : []);
}

const cache = new Map<string, Template>();

function compile(format: string): Template {
	let template = cache.get(format);
	if (template === undefined) {
		template = parse(format);
		if (cache.size >= CACHE_SIZE) {
			cache.delete(cache.keys().next().value!);
		}
		cache.set(format, template);
	}
	return template;
}

/**
 * Hand-written equivalent of sprintf-js' placeholder regex:
 * /^%(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/
 */
function parse(format: string): Template {
	const literals: string[] = [];
	const placeholders: Placeholder[] = [];
	let literal = "";
	let usesNames = false;
	let usesPositions = false;
	let i = 0;

	while (i < format.length) {
		const start = format.indexOf("%", i);
		if (start === -1) {
			literal += format.slice(i);
			break;
		}
		literal += format.slice(i, start);
		i = start + 1;
		if (format.charCodeAt(i) === PERCENT) {
			literal += "%";
			i++;
			continue;
		}

		let index = -1;
		let name: string | null = null;
		let c = format.charCodeAt(i);
		if (c > ZERO && c <= ZERO + 9) {
			let j = i + 1;
			while (isDigit(format.charCodeAt(j))) j++;
			// Without the "$", these digits are the width.
			if (format.charCodeAt(j) === DOLLAR) {
				index = Number(format.slice(i, j)) - 1;
				i = j + 1;
			}
		} else if (c === OPEN_PAREN) {
			const close = format.indexOf(")", i + 1);
			if (close > i + 1) {
				name = format.slice(i + 1, close);
				i = close + 1;
			}
		}

		let plus = false;
		if (format.charCodeAt(i) === PLUS) {
			plus = true;
			i++;
		}

		let padChar = " ";
		c = format.charCodeAt(i);
		if (c === ZERO) {
			padChar = "0";
			i++;
		} else if (c === QUOTE && i + 1 < format.length && format.charCodeAt(i + 1) !== DOLLAR) {
			padChar = format.charAt(i + 1);
			i += 2;
		}

		let left = false;
		if (format.charCodeAt(i) === MINUS) {
			left = true;
			i++;
		}

		let width = 0;
		while (isDigit((c = format.charCodeAt(i)))) {
			width = width * 10 + c - ZERO;
			i++;
		}

		let precision = -1;
		if (format.charCodeAt(i) === DOT && isDigit(format.charCodeAt(i + 1))) {
			precision = 0;
			i++;
			while (isDigit((c = format.charCodeAt(i)))) {
				precision = precision * 10 + c - ZERO;
				i++;
			}
		}

		const type = format.charAt(i);
		if (type === "" || !CONVERSIONS.includes(type)) {
			throw new SyntaxError("[sprintf] unexpected placeholder");
		}
		i++;

		const keys = name === null ? null : parseKeys(name);
		if (keys === null) {
			usesPositions = true;
		} else {
			usesNames = true;
		}
		if (usesNames && usesPositions) {
			throw new Error("[sprintf] mixing positional and named placeholders is not (yet) supported");
		}

		literals.push(literal);
		literal = "";
		placeholders.push({
			type: type.charCodeAt(0),
			index,
			keys,
			plus,
			left,
			padChar,
			width: Math.min(width, MAX_WIDTH),
			precision,
		});
	}

	literals.push(literal);
	return { literals, placeholders };
}

function isDigit(c: number): boolean {
	return c >= ZERO && c <= ZERO + 9;
}

function parseKeys(name: string): string[] {
	let match = KEY.exec(name);
	if (match === null) {
		throw new SyntaxError("[sprintf] failed to parse named argument key");
	}
	const keys = [match[1]];
	let rest = name.slice(match[0].length);
	while (rest !== "") {
		match = KEY_ACCESS.exec(rest) ?? INDEX_ACCESS.exec(rest);
		if (match === null) {
			throw new SyntaxError("[sprintf] failed to parse named argument key");
		}
		keys.push(match[1]);
		rest = rest.slice(match[0].length);
	}
	return keys;
}

function render(template: Template, args: ArrayLike<unknown>): string {
	const { literals, placeholders } = template;
	let out = literals[0];
	let cursor = 0;
	for (let i = 0; i < placeholders.length; i++) {
		const ph = placeholders[i];
		let arg: unknown;
		if (ph.keys !== null) {
			arg = resolve(args[0], ph.keys);
		} else if (ph.index >= 0) {
			arg = args[ph.index];
		} else {
			arg = args[cursor++];
		}
		out += convert(ph, arg);
		out += literals[i + 1];
	}
	return out;
}

function resolve(arg: unknown, keys: string[]): unknown {
	for (let k = 0; k < keys.length; k++) {
		if (arg == null) {
			throw new Error(`[sprintf] Cannot access property "${keys[k]}" of undefined value "${keys[k - 1]}"`);
		}
		arg = (arg as Record<string, unknown>)[keys[k]];
	}
	return arg;
}

function convert(ph: Placeholder, arg: unknown): string {
	const type = ph.type;
	if (type !== TYPE && type !== VALUE && arg instanceof Function) {
		arg = arg();
	}

	let value: string;
	switch (type) {
		case STRING:
			value = typeof arg === "string" ? arg : String(arg);
			break;
		case BOOLEAN:
			value = String(!!arg);
			break;
		case TYPE:
			value = typeOf(arg);
			break;
		case VALUE:
			value = arg == null ? String(arg) : "" + (arg as object).valueOf();
			break;
		case JSON_:
			return String(JSON.stringify(arg, null, ph.width));
		case OCTAL: // Not type-checked by sprintf-js either: "%o" of undefined is "0".
			return pad(ph, "", (toInt(arg) >>> 0).toString(8));
		default:
			return convertNumber(ph, arg);
	}
	if (ph.precision > 0) {
		value = value.substring(0, ph.precision);
	}
	return pad(ph, "", value);
}

function convertNumber(ph: Placeholder, arg: unknown): string {
	if (typeof arg !== "number" && isNaN(arg as number)) {
		throw new TypeError(`[sprintf] expecting number but found ${typeOf(arg)}`);
	}

	switch (ph.type) {
		case BINARY:
			return pad(ph, "", toInt(arg).toString(2));
		case CHAR:
			return pad(ph, "", String.fromCharCode(toInt(arg)));
		case UNSIGNED:
			return pad(ph, "", String(toInt(arg) >>> 0));
		case HEX:
			return pad(ph, "", (toInt(arg) >>> 0).toString(16));
		case HEX_UPPER:
			return pad(ph, "", (toInt(arg) >>> 0).toString(16).toUpperCase());
	}

	// The sign is decided on the argument, not the converted value: "%d" of -0.5 is "-0".
	const positive = (arg as number) >= 0;
	let value: string;
	switch (ph.type) {
		case DECIMAL:
		case INTEGER:
			value = String(toInt(arg));
			break;
		case EXPONENT:
			value = ph.precision >= 0 ? toFloat(arg).toExponential(Math.min(ph.precision, MAX_PRECISION)) : toFloat(arg).toExponential();
			break;
		case FIXED:
			value = ph.precision >= 0 ? toFloat(arg).toFixed(Math.min(ph.precision, MAX_PRECISION)) : String(toFloat(arg));
			break;
		case GENERAL:
		default:
			// A precision of 0 means 1, as in C.
			value =
				ph.precision >= 0
					? String(Number(toFloat(arg).toPrecision(Math.max(1, Math.min(ph.precision, MAX_PRECISION)))))
					: String(toFloat(arg));
	}

	if (positive && !ph.plus) {
		return pad(ph, "", value);
	}
	const first = value.charCodeAt(0);
	if (first === MINUS || first === PLUS) {
		value = value.slice(1);
	}
	return pad(ph, positive ? "+" : "-", value);
}

/** parseInt(arg, 10), skipping the string round-trip for int32 numbers. */
function toInt(arg: unknown): number {
	return typeof arg === "number" && (arg | 0) === arg ? arg : parseInt(arg as string, 10);
}

/** parseFloat(arg). For numbers that is the identity, except -0 which becomes 0 (hence + 0). */
function toFloat(arg: unknown): number {
	return typeof arg === "number" ? arg + 0 : parseFloat(arg as string);
}

function typeOf(arg: unknown): string {
	return Object.prototype.toString.call(arg).slice(8, -1).toLowerCase();
}

function pad(ph: Placeholder, sign: string, value: string): string {
	const fill = ph.width - sign.length - value.length;
	if (fill <= 0) {
		return sign + value;
	}
	const padding = ph.padChar.repeat(fill);
	if (ph.left) {
		return sign + value + padding;
	}
	return ph.padChar === "0" ? sign + padding + value : padding + sign + value;
}
