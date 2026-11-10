<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\Insights;

use App\Actions\Insights\Helpers\LocalCaptureTime;
use Tests\AbstractTestCase;

/**
 * Covers Feature 085's clock rule (FR-085-15, S-085-15): stored UTC capture
 * times are read in the timezone they were taken in.
 */
class LocalCaptureTimeTest extends AbstractTestCase
{
	public function testOffsetMovesTheDateAcrossTheYearBoundary(): void
	{
		$moment = (new LocalCaptureTime())->convert('2024-12-31 23:30:00', '+09:00');

		self::assertSame('2025-01-01', $moment->date);
		self::assertSame(2025, $moment->year);
		self::assertSame(1, $moment->month);
		self::assertSame(8, $moment->hour);
		self::assertSame('08:30', $moment->time);
		self::assertSame(2, $moment->weekday); // Wednesday, Monday = 0
	}

	public function testIdentifierFollowsDaylightSavingTime(): void
	{
		$clock = new LocalCaptureTime();

		self::assertSame(13, $clock->convert('2025-01-15 12:00:00', 'Europe/Paris')->hour);
		self::assertSame(14, $clock->convert('2025-07-15 12:00:00', 'Europe/Paris')->hour);
	}

	public function testMicrosecondsAreAccepted(): void
	{
		$moment = (new LocalCaptureTime())->convert('2025-03-10 10:15:00.123456', 'UTC');

		self::assertSame('2025-03-10', $moment->date);
		self::assertSame(10, $moment->hour);
	}

	public function testMissingTimezoneKeepsTheStoredTime(): void
	{
		$moment = (new LocalCaptureTime())->convert('2025-03-10 23:15:00', null);

		self::assertSame('2025-03-10', $moment->date);
		self::assertSame(23, $moment->hour);
	}

	public function testUnknownTimezoneKeepsTheStoredTime(): void
	{
		$moment = (new LocalCaptureTime())->convert('2025-03-10 23:15:00', 'Mars/Olympus_Mons');

		self::assertSame('2025-03-10', $moment->date);
		self::assertSame(23, $moment->hour);
	}

	public function testWeekStartsOnMondayAcrossTheYearBoundary(): void
	{
		// Sunday 2027-01-03 belongs to ISO week 2026-W53, which starts on Monday 2026-12-28.
		$moment = (new LocalCaptureTime())->convert('2027-01-03 12:00:00', 'UTC');

		self::assertSame('2026-12-28', $moment->week_start);
		self::assertSame(6, $moment->weekday);
	}

	public function testWeekBefore1970(): void
	{
		// Wednesday 1969-12-31: negative timestamps must round towards the earlier day.
		$moment = (new LocalCaptureTime())->convert('1969-12-31 12:00:00', 'UTC');

		self::assertSame('1969-12-31', $moment->date);
		self::assertSame(2, $moment->weekday);
		self::assertSame('1969-12-29', $moment->week_start);
	}

	public function testPositiveOffsetStartsTheNextWeek(): void
	{
		// Sunday 2025-03-09 20:00 UTC is Monday 10:00 at +14:00.
		$moment = (new LocalCaptureTime())->convert('2025-03-09 20:00:00', '+14:00');

		self::assertSame('2025-03-10', $moment->date);
		self::assertSame(0, $moment->weekday);
		self::assertSame('2025-03-10', $moment->week_start);
		self::assertSame(10, $moment->hour);
	}

	public function testNegativeOffsetStaysInThePreviousWeek(): void
	{
		// Monday 2025-03-10 05:00 UTC is Sunday 17:00 at -12:00.
		$moment = (new LocalCaptureTime())->convert('2025-03-10 05:00:00', '-12:00');

		self::assertSame('2025-03-09', $moment->date);
		self::assertSame(6, $moment->weekday);
		self::assertSame('2025-03-03', $moment->week_start);
		self::assertSame(17, $moment->hour);
	}

	public function testHalfHourZoneMovesMinutesAndDate(): void
	{
		// India is +05:30: 18:45 UTC is 00:15 the next day.
		$moment = (new LocalCaptureTime())->convert('2025-03-10 18:45:00', 'Asia/Kolkata');

		self::assertSame('2025-03-11', $moment->date);
		self::assertSame(2025, $moment->year);
		self::assertSame(3, $moment->month);
		self::assertSame(0, $moment->hour);
		self::assertSame('00:15', $moment->time);
	}

	public function testOffsetBeyondFourteenHoursKeepsTheStoredTime(): void
	{
		// +15:00 would give 2026-01-01 00:30, outside the window of either year (Feature 108, FR-108-04).
		$moment = (new LocalCaptureTime())->convert('2025-12-31 09:30:00', '+15:00');

		self::assertSame('2025-12-31', $moment->date);
		self::assertSame(2025, $moment->year);
		self::assertSame(9, $moment->hour);
	}

	public function testLocalMeanTimeBeyondFourteenHoursKeepsTheStoredTime(): void
	{
		// Manila kept American dates (-15:56) until 1845.
		$moment = (new LocalCaptureTime())->convert('1800-06-01 12:00:00', 'Asia/Manila');

		self::assertSame('1800-06-01', $moment->date);
		self::assertSame(12, $moment->hour);
	}

	public function testFourteenHoursIsAValidOffset(): void
	{
		$moment = (new LocalCaptureTime())->convert('2025-06-01 12:00:00', 'Pacific/Kiritimati');

		self::assertSame('2025-06-02', $moment->date);
		self::assertSame(2, $moment->hour);
	}
}
