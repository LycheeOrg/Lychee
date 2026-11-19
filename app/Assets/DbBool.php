<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Assets;

/**
 * Normalizes raw, non-Eloquent-cast boolean column values into PHP bool.
 *
 * PostgreSQL's PDO driver returns `boolean` columns as the strings `"t"`/`"f"`
 * rather than `"1"`/`"0"` (MySQL/SQLite), and `filter_var(..., FILTER_VALIDATE_BOOLEAN)`
 * does not recognize `"t"` as true — it silently reads every true value as false.
 * Use {@see self::parse()} instead wherever a boolean is read off a
 * `toBase()`/query-builder row (Eloquent models already cast correctly via `$casts`).
 */
class DbBool
{
	public static function parse(mixed $value): bool
	{
		return match ($value) {
			true, 1, '1', 't' => true,
			default => false,
		};
	}
}
