<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use Safe\DateTimeImmutable;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;

/**
 * Row-intrinsic bucket keys (ADR-092-01, ADR-092-02): pure static
 * functions called explicitly at every write site, never wired through an
 * Eloquent event, mutator or cast.
 *
 * Every date key is fixed-width (`Y-m-d-H` or `Y-m-d`), so a bucket id is
 * a prefix of the key and a bucket is a key range.
 */
final class BucketKeys
{
	public const HOUR_KEY_FORMAT = 'Y-m-d-H';

	private const TITLE_DATE_PATTERN = '/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?/';

	/**
	 * `photos.taken_key` of a cast `taken_at`, which is already in the zone
	 * the photo was taken in (`taken_at_orig_tz`).
	 */
	public static function takenKey(?\DateTimeInterface $taken_at): ?string
	{
		return $taken_at?->format(self::HOUR_KEY_FORMAT);
	}

	/**
	 * `photos.taken_key` of a raw stored `taken_at` (UTC, `Y-m-d H:i:s[.u]`)
	 * and its `taken_at_orig_tz`; a missing zone keeps UTC.
	 */
	public static function takenKeyFromRaw(?string $raw_taken_at, ?string $orig_tz): ?string
	{
		if ($raw_taken_at === null) {
			return null;
		}

		$utc = new \DateTimeZone('UTC');
		$zone = ($orig_tz === null || $orig_tz === '') ? $utc : new \DateTimeZone($orig_tz);

		return (new DateTimeImmutable($raw_taken_at, $utc))->setTimezone($zone)->format(self::HOUR_KEY_FORMAT);
	}

	/**
	 * Key of a raw stored `created_at` (`Y-m-d H:i:s[.u]`), as stored, to
	 * the hour.
	 */
	public static function createdKey(string $raw_created_at): string
	{
		return substr($raw_created_at, 0, 10) . '-' . substr($raw_created_at, 11, 2);
	}

	/**
	 * `photos.title_date_key`: the leading `YYYY`, `YYYY-MM` or `YYYY-MM-DD`
	 * of the trimmed title as `Y-m-d` (missing month and day read as `01`),
	 * or `null` when there is none or it is not a calendar date.
	 */
	public static function titleDateKey(string $title): ?string
	{
		try {
			if (preg_match(self::TITLE_DATE_PATTERN, trim($title), $matches) !== 1) {
				return null;
			}
			// @codeCoverageIgnoreStart
		} catch (PcreException) {
			return null;
		}
		// @codeCoverageIgnoreEnd

		$year = $matches[1];
		$month = ($matches[2] ?? '') === '' ? '01' : $matches[2];
		$day = ($matches[3] ?? '') === '' ? '01' : $matches[3];

		if (!checkdate((int) $month, (int) $day, (int) $year)) {
			return null;
		}

		return $year . '-' . $month . '-' . $day;
	}
}
