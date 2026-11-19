<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

use App\DTO\Insights\InsightsPeriod;
use Safe\DateTimeImmutable;

/**
 * Converts a stored capture time (UTC, as written by `UTCBasedTimes`) to the
 * timezone recorded in `taken_at_orig_tz` (Feature 085, FR-085-15).
 *
 * A missing or unknown timezone keeps the stored time, and so does one more
 * than 14 hours from UTC at that instant (Feature 108, FR-108-04). Timezone
 * objects are cached per string because a library uses only a handful of them.
 */
final class LocalCaptureTime
{
	private const SECONDS_PER_DAY = 86400;

	private const MAX_OFFSET_SECONDS = InsightsPeriod::MAX_OFFSET_HOURS * 3600;

	private \DateTimeZone $utc;

	/** @var array<string,\DateTimeZone> */
	private array $zones = [];

	public function __construct()
	{
		$this->utc = new \DateTimeZone('UTC');
	}

	/**
	 * @param string      $utc_datetime SQL datetime in UTC
	 * @param string|null $timezone     original timezone (offset or identifier)
	 */
	public function convert(string $utc_datetime, ?string $timezone): LocalMoment
	{
		// Parsed once by Safe, shifted and read on the native object: Safe's
		// own setTimezone() parses the value twice more (Feature 108, NFR-108-03).
		$local = self::shift((new DateTimeImmutable($utc_datetime, $this->utc))->getInnerDateTime(), $this->zone($timezone));
		[$date, $year, $month, $iso_weekday, $hour, $time] = explode('|', $local->format('Y-m-d|Y|n|N|G|H:i'));
		$weekday = intval($iso_weekday) - 1;

		return new LocalMoment(
			date: $date,
			year: intval($year),
			month: intval($month),
			week_start: self::weekStart($local, $weekday),
			weekday: $weekday,
			hour: intval($hour),
			time: $time,
		);
	}

	/**
	 * The UTC time in `$zone`, or unchanged when the zone is more than 14
	 * hours from UTC at that instant (an offset such as `+15:00`, or a local
	 * mean time before standard time): the UTC windows of a Year or Range
	 * allow for 14 hours at most, so a wider shift would count the photo in
	 * a period whose window does not hold it.
	 */
	private static function shift(\DateTimeImmutable $utc, \DateTimeZone $zone): \DateTimeImmutable
	{
		$local = $utc->setTimezone($zone);

		return abs($local->getOffset()) > self::MAX_OFFSET_SECONDS ? $utc : $local;
	}

	/**
	 * Monday of the local week, counted in whole local days since 1970-01-01
	 * (floored, so a day before 1970 is not rounded towards the next one).
	 */
	private static function weekStart(\DateTimeImmutable $local, int $weekday): string
	{
		$day = intval(floor(($local->getTimestamp() + $local->getOffset()) / self::SECONDS_PER_DAY));

		return gmdate('Y-m-d', ($day - $weekday) * self::SECONDS_PER_DAY);
	}

	private function zone(?string $timezone): \DateTimeZone
	{
		if ($timezone === null || $timezone === '') {
			return $this->utc;
		}

		return $this->zones[$timezone] ??= $this->parseZone($timezone);
	}

	private function parseZone(string $timezone): \DateTimeZone
	{
		try {
			return new \DateTimeZone($timezone);
		} catch (\Exception) {
			return $this->utc;
		}
	}
}
