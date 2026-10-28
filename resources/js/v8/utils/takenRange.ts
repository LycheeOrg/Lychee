/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * The capture range of an album (`min_taken_at` … `max_taken_at`, sub-albums
 * included) as shown on the bulk edit table: "from – to", one date when both
 * fall on the same day, null without capture date (Feature 103, FR-103-15).
 */
export function takenRangeLabel(min: string | null, max: string | null, format: (date: string) => string): string | null {
	const from = min === null ? null : format(min);
	const to = max === null ? null : format(max);
	if (from === null || to === null || from === to) {
		return from ?? to;
	}
	return `${from} – ${to}`;
}
