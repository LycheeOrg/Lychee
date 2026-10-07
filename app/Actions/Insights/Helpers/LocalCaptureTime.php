<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Helpers;

use Safe\DateTimeImmutable;

/**
 * Converts a stored capture time (UTC, as written by `UTCBasedTimes`) to the
 * timezone recorded in `taken_at_orig_tz` (Feature 085, FR-085-15).
 *
 * A missing or unknown timezone keeps the stored time. Timezone objects are
 * cached per string because a library uses only a handful of them.
 */
final class LocalCaptureTime
{
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
		$local = (new DateTimeImmutable($utc_datetime, $this->utc))->setTimezone($this->zone($timezone));
		$weekday = intval($local->format('N')) - 1;

		return new LocalMoment(
			date: $local->format('Y-m-d'),
			year: intval($local->format('Y')),
			month: intval($local->format('n')),
			week_start: $local->modify('-' . $weekday . ' days')->format('Y-m-d'),
			weekday: $weekday,
			hour: intval($local->format('G')),
			time: $local->format('H:i'),
		);
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
