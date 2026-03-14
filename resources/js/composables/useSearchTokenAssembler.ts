/**
 * Pure token assembler / parser for the advanced search panel.
 *
 * `assembleTokens` converts an AdvancedSearchState struct into a raw token string
 * that matches the backend SearchTokenParser grammar.
 *
 * `parseTokens` reverses the process: it splits a raw string into known token
 * modifiers (populating AdvancedSearchState) and an unrecognised remainder.
 */

export interface AdvancedSearchState {
	title: string;
	description: string;
	location: string;
	/** Comma-separated tag names, e.g. "sunset, beach" */
	tags: string;
	/** YYYY-MM-DD or "" */
	dateFrom: string;
	/** YYYY-MM-DD or "" */
	dateTo: string;
	/** "image" | "video" | "raw" | "live" | "" */
	type: string;
	/** "landscape" | "portrait" | "square" | "" */
	orientation: string;
	/** "0"–"5" or "" */
	ratingMin: string;
	/** "0"–"5" or "" – only emitted when isAuthenticated is true */
	ratingOwn: string;
	make: string;
	model: string;
	lens: string;
	aperture: string;
	shutter: string;
	focal: string;
	iso: string;
}

export function emptyAdvancedSearchState(): AdvancedSearchState {
	return {
		title: "",
		description: "",
		location: "",
		tags: "",
		dateFrom: "",
		dateTo: "",
		type: "",
		orientation: "",
		ratingMin: "",
		ratingOwn: "",
		make: "",
		model: "",
		lens: "",
		aperture: "",
		shutter: "",
		focal: "",
		iso: "",
	};
}

// ---------------------------------------------------------------------------
// Assembly
// ---------------------------------------------------------------------------

/**
 * Assembles a single `modifier:value` token.
 * When the value contains spaces the *entire* token is wrapped in double-quotes
 * so that the backend tokeniser (`"[^"]*"|\S+`) can consume it as one unit.
 */
function assembleStringToken(modifier: string, value: string): string {
	const token = `${modifier}:${value}`;
	return value.includes(" ") ? `"${token}"` : token;
}

/**
 * Converts an AdvancedSearchState into a space-separated token string.
 *
 * @param state           The current advanced search state.
 * @param isAuthenticated Whether the current user is logged in; required to
 *                        emit the `rating:own:` token.
 */
export function assembleTokens(state: AdvancedSearchState, isAuthenticated: boolean): string {
	const parts: string[] = [];

	if (state.title.trim()) parts.push(assembleStringToken("title", state.title.trim()));
	if (state.description.trim()) parts.push(assembleStringToken("description", state.description.trim()));
	if (state.location.trim()) parts.push(assembleStringToken("location", state.location.trim()));

	for (const tag of state.tags
		.split(",")
		.map((t) => t.trim())
		.filter(Boolean)) {
		parts.push(assembleStringToken("tag", tag));
	}

	if (state.dateFrom) parts.push(`date:>=${state.dateFrom}`);
	if (state.dateTo) parts.push(`date:<=${state.dateTo}`);
	if (state.type) parts.push(`type:${state.type}`);
	if (state.orientation) parts.push(`ratio:${state.orientation}`);
	if (state.ratingMin) parts.push(`rating:avg:>=${state.ratingMin}`);
	if (state.ratingOwn && isAuthenticated) parts.push(`rating:own:>=${state.ratingOwn}`);

	if (state.make.trim()) parts.push(assembleStringToken("make", state.make.trim()));
	if (state.model.trim()) parts.push(assembleStringToken("model", state.model.trim()));
	if (state.lens.trim()) parts.push(assembleStringToken("lens", state.lens.trim()));
	if (state.aperture.trim()) parts.push(`aperture:${state.aperture.trim()}`);
	if (state.shutter.trim()) parts.push(`shutter:${state.shutter.trim()}`);
	if (state.focal.trim()) parts.push(`focal:${state.focal.trim()}`);
	if (state.iso.trim()) parts.push(`iso:${state.iso.trim()}`);

	return parts.join(" ");
}

// ---------------------------------------------------------------------------
// Parsing (reverse direction)
// ---------------------------------------------------------------------------

/**
 * Splits a raw query string into space-separated tokens, respecting
 * double-quoted phrases (which may contain spaces).
 */
function tokenizeRaw(raw: string): string[] {
	const tokens: string[] = [];
	// Matches either a quoted phrase or a run of non-whitespace non-quote chars,
	// combined together (handles modifier:"quoted value" as one token).
	const re = /(?:"[^"]*"|[^\s"])+/g;
	let m: RegExpExecArray | null;
	while ((m = re.exec(raw)) !== null) {
		tokens.push(m[0]);
	}
	return tokens;
}

/** Strips surrounding double-quotes from a value string if present. */
function stripQuotes(value: string): string {
	if (value.startsWith('"') && value.endsWith('"') && value.length >= 2) {
		return value.slice(1, -1);
	}
	return value;
}

/**
 * Parses a raw token string back into an AdvancedSearchState plus a remainder
 * string for tokens that could not be mapped to a known advanced field.
 *
 * @param raw  The full raw query string from the simple input.
 * @returns    `advanced` – the populated AdvancedSearchState.
 *             `remainder` – space-joined string of unrecognised tokens.
 */
export function parseTokens(raw: string): { advanced: AdvancedSearchState; remainder: string } {
	const advanced = emptyAdvancedSearchState();
	const remainderParts: string[] = [];
	const tagAccumulator: string[] = [];

	for (const rawToken of tokenizeRaw(raw)) {
		// Unwrap whole-token quoting: "modifier:value with spaces" → modifier:value with spaces
		// Plain tokens and value-quoted tokens (modifier:"value") are unchanged by stripQuotes.
		const token = stripQuotes(rawToken);
		const lower = token.toLowerCase();

		if (lower.startsWith("title:")) {
			advanced.title = stripQuotes(token.slice(6));
		} else if (lower.startsWith("description:")) {
			advanced.description = stripQuotes(token.slice(12));
		} else if (lower.startsWith("location:")) {
			advanced.location = stripQuotes(token.slice(9));
		} else if (lower.startsWith("tag:")) {
			tagAccumulator.push(stripQuotes(token.slice(4)));
		} else if (lower.startsWith("date:>=")) {
			advanced.dateFrom = token.slice(7);
		} else if (lower.startsWith("date:<=")) {
			advanced.dateTo = token.slice(7);
		} else if (lower.startsWith("type:")) {
			advanced.type = token.slice(5).toLowerCase();
		} else if (lower.startsWith("ratio:")) {
			advanced.orientation = token.slice(6).toLowerCase();
		} else if (lower.startsWith("rating:avg:>=")) {
			advanced.ratingMin = token.slice(13);
		} else if (lower.startsWith("rating:own:>=")) {
			advanced.ratingOwn = token.slice(13);
		} else if (lower.startsWith("make:")) {
			advanced.make = stripQuotes(token.slice(5));
		} else if (lower.startsWith("model:")) {
			advanced.model = stripQuotes(token.slice(6));
		} else if (lower.startsWith("lens:")) {
			advanced.lens = stripQuotes(token.slice(5));
		} else if (lower.startsWith("aperture:")) {
			advanced.aperture = token.slice(9);
		} else if (lower.startsWith("shutter:")) {
			advanced.shutter = token.slice(8);
		} else if (lower.startsWith("focal:")) {
			advanced.focal = token.slice(6);
		} else if (lower.startsWith("iso:")) {
			advanced.iso = token.slice(4);
		} else {
			remainderParts.push(rawToken);
		}
	}

	if (tagAccumulator.length > 0) {
		advanced.tags = tagAccumulator.join(", ");
	}

	return { advanced, remainder: remainderParts.join(" ") };
}
