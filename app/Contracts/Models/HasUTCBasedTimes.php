<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Models;

use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Support\Carbon;

interface HasUTCBasedTimes
{
	/**
	 * Converts a DateTime to a storable SQL datetime string.
	 *
	 * This method fixes Model#fromDateTime.
	 * The returned SQL datetime string without a timezone indication always
	 * represents an instant of time relative to
	 * {@link UTCBasedTimes::$DB_TIMEZONE_NAME}.
	 * The original method simply cuts off any timezone information from the
	 * input.
	 *
	 * If the input string has a recognized string format but without a
	 * timezone indication, e.g. something like `YYYY-MM-DD hh:mm:ss`, then
	 * the input string is interpreted as a "wall time" relative to
	 * {@link UTCBasedTimes::$DB_TIMEZONE_NAME}.
	 * As a result, the input string and returned string represent the same
	 * "wall time" without any conversion.
	 * However, the input string and returned string may still differ and
	 * have different string values due to normalization, e.g. the input
	 * string '2020-1-1 8:17' is returned as '2020-01-01 08:17:00'.
	 *
	 * For any input type which has a timezone information (e.g. objects
	 * which inherit \DateTimeInterface, string with explicit timezone
	 * information, etc.) the original timezone is respected and the result
	 * is properly converted to {@link UTCBasedTimes::$DB_TIMEZONE_NAME}.
	 *
	 * @throws InvalidTimeZoneException
	 */
	public function fromDateTime($value): ?string;

	/**
	 * Returns a Carbon object.
	 *
	 * This method fixes Model#asDateTime.
	 * For any input without an explicit timezone, the input time is
	 * interpreted relative to {@link UTCBasedTimes::$DB_TIMEZONE_NAME}.
	 * The returned Carbon object uses the application's default timezone
	 * with the date/time properly converted from
	 * {@link UTCBasedTimes::$DB_TIMEZONE_NAME} to `date_default_timezone_get()`.
	 *
	 * In particular, the following holds:
	 *  - If the input value is already a DateTime object (i.e. implements
	 *    \DateTimeInterface), then a new instance of Carbon is returned which
	 *    represents the same date/time and timezone as the input object.
	 *    As the return value is a new instance, it is safe to alter the
	 *    return value without modifying the original object.
	 *  - If the input is an integer, the input is interpreted as seconds
	 *    since epoch (in UTC) and the newly created Carbon object uses the
	 *    application's default timezone.
	 *    In other words, if the input value equals 0 and the application's
	 *    default timezone is `CET`, then the Carbon object will be
	 *    `Carbon\Carbon{ time: '1970-01-01 01:00:00', timezone: 'CET' }`.
	 *  - If the input value is a string _with_ a timezone information, the
	 *    Carbon object will represent that string using the original timezone
	 *    as given by the string.
	 *  - If the input value is a string _without_ a timezone information,
	 *    then the given datetime string is interpreted relative to
	 *    {@link UTCBasedTimes::$DB_TIMEZONE_NAME} and the returned Carbon object uses
	 *    the application's default timezone.
	 *    In other words, if the input value equals '1970-01-01 00:00:00' and
	 *    the application's default timezone is CET, then the Carbon object
	 *    will be
	 *    `Carbon\Carbon{ time: '1970-01-01 01:00:00', timezone: 'CET' }`.
	 *
	 * @throws InvalidTimeZoneException
	 */
	public function asDateTime($value): Carbon;
}