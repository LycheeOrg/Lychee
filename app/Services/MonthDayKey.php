<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

/**
 * `photos.month_day_key` (FR-106-01): month × 100 + day, read by the On This
 * Day smart album through `photos_month_day_key_index`.
 */
final class MonthDayKey
{
	/**
	 * The day of a cast `taken_at`, which is in the zone the photo was taken
	 * in (the date of `photos.taken_key`), else of `created_at` as stored.
	 */
	public static function of(?\DateTimeInterface $taken_at, \DateTimeInterface $created_at): int
	{
		return self::ofDate($taken_at ?? $created_at);
	}

	/**
	 * The day of `$date` in its own zone.
	 */
	public static function ofDate(\DateTimeInterface $date): int
	{
		return (int) $date->format('n') * 100 + (int) $date->format('j');
	}
}
