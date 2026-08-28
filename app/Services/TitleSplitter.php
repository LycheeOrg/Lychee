<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\DTO\TitleSplitResult;
use function Safe\preg_match;

/**
 * Splits a `title` string into a case-folded, sortable `base` and an
 * optional trailing numeric `index`, so that a plain `ORDER BY base, index`
 * reproduces natural-sort-like ordering (`test_0, test_1, test_2, test_10`)
 * purely at the database layer, without regex/locale support in SQL
 * (see spec 060 FR-060-02, NFR-060-01).
 *
 * Deliberately a hardcoded, non-pluggable 2-rule chain (spec Non-Goals) and
 * a pure static function, called explicitly at every write site (FR-060-03)
 * — never wired via an Eloquent model event/hook.
 */
final class TitleSplitter
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
		// from a title that has no numeric suffix at all.
		return new TitleSplitResult(mb_strtolower($title), null);
	}

	private static function toResult(string $base, string $digits, ?string $extension): TitleSplitResult
	{
		if (strlen($digits) > self::MAX_INDEX_DIGITS) {
			$digits = substr($digits, -self::MAX_INDEX_DIGITS);
		}

		return new TitleSplitResult(mb_strtolower($base . ($extension ?? '')), (int) $digits);
	}
}
