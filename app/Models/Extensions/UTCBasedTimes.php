<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Exceptions\Internal\LycheeLogicException;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;
use function Safe\preg_match;

/**
 * Trait UTCBasedTimes.
 *
 * This model stores all timestamps without timezone information relative to
 * UTC in the database.
 *
 * This class patches the base Model class of Laravel with respect to
 * hydration/de-hydration of Carbon time object from/to database.
 * This patch is inspired by https://github.com/laravel/framework/issues/1841.
 * See https://github.com/laravel/framework/issues/1841#issuecomment-846405008
 * for a summary of discussion and an illustration of the problem.
 *
 * **Attention:**
 * For this class to work properly, the timezone of the database connection
 * must be set to UTC for those RDBM systems (e.g. PostgreSQL, MySQL) which
 * support "timezone aware" database connections.
 * This means the database configuration for MySQL should explicitly include
 * the option `'timezone' => '+00:00'` and the configuration for PostgreSQL
 * should explicitly include the option `'timezone => 'UTC'`.
 * Otherwise, those RDBM systems interpret an SQL datetime string without an
 * explicit timezone relative to their own default timezone.
 * The default timezone of the database connection might or might not
 * be UTC and might or might not be equal to the default timezone of the PHP
 * application.
 * Hence, it is always a good thing to set the timezone of the database
 * connection explicitly.
 * Note, this is not an issue for SQLite which does not support a default
 * timezone for the database connection and always assumes that SQL datetime
 * strings without a timezone are given relative to UTC.
 */
trait UTCBasedTimes
{
	private static string $DB_TIMEZONE_NAME = 'UTC';
	private static string $DB_DATETIME_FORMAT = 'Y-m-d H:i:s.u';
	private static string $STANDARD_DATE_PATTERN = '/^(\d{4})-(\d{1,2})-(\d{1,2})$/';

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
	 * @param mixed $value
	 *
	 * @return string|null
	 *
	 * @throws InvalidTimeZoneException
	 */
	public function fromDateTime($value): ?string
	{
		// If $value is already an instance of Carbon, the method returns a
		// deep copy, hence it is safe to change the timezone below without
		// altering the original object
		if ($value === null || $value === '') {
			return null;
		}

		$carbonTime = $this->asDateTime($value);
		$carbonTime->setTimezone(self::$DB_TIMEZONE_NAME);

		return $carbonTime->format(self::$DB_DATETIME_FORMAT);
	}

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
	 * @param mixed $value
	 *
	 * @return Carbon
	 *
	 * @throws InvalidTimeZoneException
	 */
	public function asDateTime($value): Carbon
	{
		if ($value === null || $value === '') {
			// @codeCoverageIgnoreStart
			throw new LycheeLogicException('asDateTime called on null or empty string');
			// @codeCoverageIgnoreEnd
		}

		// If this value is already a Carbon instance, we shall just return it as is.
		// This prevents us having to re-instantiate a Carbon instance when we know
		// it already is one, which wouldn't be fulfilled by the DateTime check.
		if ($value instanceof CarbonInterface) {
			return Date::instance($value);
		}

		// If the value is already a DateTime instance, we will just skip the rest of
		// these checks since they will be a waste of time, and hinder performance
		// when checking the field. We will just return the DateTime right away.
		if ($value instanceof \DateTimeInterface) {
			// @codeCoverageIgnoreStart
			return Date::parse(
				$value->format('Y-m-d H:i:s.u'),
				$value->getTimezone()
			);
			// @codeCoverageIgnoreEnd
		}

		// If this value is an integer, we will assume it is a UNIX timestamp's value
		// and format a Carbon object from this timestamp. This allows flexibility
		// when defining your date fields as they might be UNIX timestamps here.
		// Applied patch: Set bare UTC timestamp to the application's default timezone
		if (is_numeric($value)) {
			// @codeCoverageIgnoreStart
			$result = Date::createFromTimestamp($value);
			$result->setTimezone(date_default_timezone_get());

			return $result;
			// @codeCoverageIgnoreEnd
		}

		// If the value is in simply year, month, day format, we will instantiate the
		// Carbon instances from that format. Again, this provides for simple date
		// fields on the database, while still supporting Carbonized conversion.
		// Applied patch: The standard date format Y-m-d _without_ a timezone
		// is interpreted relative to UTC and _then_ set to the
		// application's default timezone.
		if (preg_match(self::$STANDARD_DATE_PATTERN, $value) === 1) {
			// @codeCoverageIgnoreStart
			$date = Date::createFromFormat('Y-m-d', $value, self::$DB_TIMEZONE_NAME);
			$date = $date !== false ? $date : null;
			$result = $date?->startOfDay();
			$result?->setTimezone(date_default_timezone_get());

			return $result;
			// @codeCoverageIgnoreEnd
		}

		// Finally, we will just assume this date is in the format used by default on
		// the database connection and use that format to create the Carbon object
		// that is returned to the caller after we convert it here.
		// Applied patch: Use 'UTC' as the default timezone for string
		// formats which do not include timezone information.
		// Note that the timezone parameter is ignored for formats which
		// include explicit timezone information.
		try {
			$result = Date::createFromFormat(self::$DB_DATETIME_FORMAT, $value, self::$DB_TIMEZONE_NAME);
			$result = $result !== false ? $result : null;
			if ($result?->getTimezone()?->getName() === self::$DB_TIMEZONE_NAME) {
				// If the timezone is different to UTC, we don't set it, because then
				// the timezone came from the input string.
				// If the timezone equals UTC, then we assume that no explicit timezone
				// information has been given, and we set it to the application's
				// default time zone.
				// This is a no-op, if the application's default timezone equals 'UTC'
				// anyway.
				// Note: There is one quirk: If the input string explicitly stated 'UTC'
				// as its timezone, then the time is still set to the app's timezone.
				$result->setTimezone(date_default_timezone_get());
			}

			return $result;
		} catch (\InvalidArgumentException) {
			// If the specified format did not mach, don't throw an exception,
			// but try to parse the value using a best-effort approach, see below
		}

		// Might throw an InvalidArgumentException if no recognized format is found,
		// but this is intended
		$result = Date::parse($value, self::$DB_TIMEZONE_NAME);
		if ($result->getTimezone()->getName() === self::$DB_TIMEZONE_NAME) {
			$result->setTimezone(date_default_timezone_get());
		}

		return $result;
	}
}
