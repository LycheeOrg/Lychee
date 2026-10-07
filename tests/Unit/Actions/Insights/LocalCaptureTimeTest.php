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
}
