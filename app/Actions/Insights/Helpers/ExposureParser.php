<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

use function Safe\preg_match;

/**
 * Reads the EXIF strings stored on `photos` as positive numbers
 * (Feature 085, FR-085-13). Anything else is `null`.
 *
 * Stored formats: `iso` "100", `aperture` "f/2.8", `focal` "200 mm",
 * `shutter` "1/320 s" or "2 s", `duration` seconds such as "12.5".
 */
final class ExposureParser
{
	private const NUMBER = '([0-9]+(?:\.[0-9]+)?)';

	public static function iso(?string $value): ?float
	{
		return self::match('/^(?:ISO\s*)?' . self::NUMBER . '$/i', $value);
	}

	public static function aperture(?string $value): ?float
	{
		return self::match('/^(?:f\/?)?\s*' . self::NUMBER . '$/i', $value);
	}

	public static function focal(?string $value): ?float
	{
		return self::match('/^' . self::NUMBER . '\s*(?:mm)?$/i', $value);
	}

	public static function duration(?string $value): ?float
	{
		return self::match('/^' . self::NUMBER . '$/', $value);
	}

	/**
	 * Exposure time in seconds.
	 */
	public static function shutter(?string $value): ?float
	{
		$matches = [];
		if ($value === null || preg_match('/^' . self::NUMBER . '(?:\/' . self::NUMBER . ')?\s*(?:s|")?$/i', trim($value), $matches) !== 1) {
			return null;
		}

		$denominator = isset($matches[2]) ? floatval($matches[2]) : 1.0;

		return self::positive($denominator > 0 ? floatval($matches[1]) / $denominator : 0.0);
	}

	private static function match(string $pattern, ?string $value): ?float
	{
		$matches = [];
		if ($value === null || preg_match($pattern, trim($value), $matches) !== 1) {
			return null;
		}

		return self::positive(floatval($matches[1]));
	}

	private static function positive(float $value): ?float
	{
		return $value > 0 ? $value : null;
	}
}
