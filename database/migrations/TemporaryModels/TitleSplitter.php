<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use function Safe\preg_match;

require_once __DIR__ . '/TitleSplitResult.php';

/**
 * Standalone copy of {@see \App\Services\TitleSplitter}, frozen for use by
 * the Feature 060 title-sorting backfill migrations.
 * Migrations must not depend on live app code, because app code may change
 * or be removed in the future while old migrations must remain runnable.
 */
class TitleSplitter
{
	private const MAX_INDEX_DIGITS = 19;

	/**
	 * Matches a trailing file-extension-shaped suffix (`.jpg`, `.xts`, ...).
	 * Requires a leading letter so a digit-only suffix (e.g. `xxx.2`) is
	 * never mistaken for an extension - `.2` is a legitimate index.
	 */
	private const EXTENSION_PATTERN = '/\.([A-Za-z][A-Za-z0-9]{0,4})$/u';

	private const TRAILING_DIGITS_PATTERN = '/^(.*?)(\d+)$/u';

	private const PARENTHESISED_NUMBER_PATTERN = '/^(.*?)\((\d+)\)$/u';

	public static function split(string $title): TitleSplitResult
	{
		// Stage A: set aside a trailing file-extension-shaped suffix, if any,
		// so it doesn't defeat the digit/paren rules below (e.g. "photo_2.jpg").
		// The extension is re-appended to `base` once an index is
		// successfully extracted (toResult()) so that titles which only
		// differ by extension (e.g. "photo_5.jpg" vs. "photo_5.heic") get
		// different `base` values and don't tie/interleave (NFR-060-09).
		if (preg_match(self::EXTENSION_PATTERN, $title, $ext_matches) === 1) {
			$extension = $ext_matches[0];
			$stem = substr($title, 0, -strlen($extension));
		} else {
			$extension = null;
			$stem = $title;
		}

		// Stage B, rule 1: trailing digit run on the (possibly stripped) stem.
		if (preg_match(self::TRAILING_DIGITS_PATTERN, $stem, $matches) === 1) {
			return self::toResult($matches[1], $matches[2], $extension);
		}

		// Stage B, rule 2: trailing parenthesised number on the stem.
		if (preg_match(self::PARENTHESISED_NUMBER_PATTERN, $stem, $matches) === 1) {
			return self::toResult($matches[1], $matches[2], $extension);
		}

		// Stage B, rule 3 (fallback): no index found even after stripping -
		// fall back to the full ORIGINAL title (extension retained), so a
		// wrong Stage-A guess (e.g. "Vol.II") never silently drops a token
		// from a title that has no numeric suffix at all. `title_index` is
		// never NULL in the database, so titles without a suffix get `0`.
		return new TitleSplitResult(mb_strtolower($title), 0);
	}

	private static function toResult(string $base, string $digits, ?string $extension): TitleSplitResult
	{
		if (strlen($digits) > self::MAX_INDEX_DIGITS) {
			$digits = substr($digits, -self::MAX_INDEX_DIGITS);
		}

		return new TitleSplitResult(mb_strtolower($base . ($extension ?? '')), (int) $digits);
	}
}
