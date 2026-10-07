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

use App\Actions\Insights\Helpers\StreakCalculator;
use Tests\AbstractTestCase;

/**
 * Covers Feature 085's time-span figures (FR-085-09, S-085-13).
 */
class StreakCalculatorTest extends AbstractTestCase
{
	public function testNoDates(): void
	{
		$summary = StreakCalculator::summarise([]);

		self::assertSame(0, $summary->days_with_photos);
		self::assertSame(0, $summary->calendar_days);
		self::assertNull($summary->longest_daily_streak);
		self::assertNull($summary->longest_weekly_streak);
		self::assertNull($summary->longest_break);
	}

	public function testSingleDay(): void
	{
		$summary = StreakCalculator::summarise(['2025-05-04']);

		self::assertSame(1, $summary->days_with_photos);
		self::assertSame(1, $summary->calendar_days);
		self::assertSame(1, $summary->longest_daily_streak?->length);
		self::assertSame('2025-05-04', $summary->longest_daily_streak->from);
		self::assertSame('2025-05-04', $summary->longest_daily_streak->to);
		self::assertSame(1, $summary->longest_weekly_streak?->length);
		self::assertSame('2025-04-28', $summary->longest_weekly_streak->from);
		self::assertNull($summary->longest_break);
	}

	public function testDailyStreakAcrossMonthEndAndLongestBreak(): void
	{
		$summary = StreakCalculator::summarise([
			'2025-01-01',
			'2025-01-02',
			'2025-01-30',
			'2025-01-31',
			'2025-02-01',
			'2025-02-02',
			'2025-02-10',
		]);

		self::assertSame(7, $summary->days_with_photos);
		self::assertSame(41, $summary->calendar_days);
		self::assertSame(4, $summary->longest_daily_streak?->length);
		self::assertSame('2025-01-30', $summary->longest_daily_streak->from);
		self::assertSame('2025-02-02', $summary->longest_daily_streak->to);
		// 2025-01-03 … 2025-01-29 have no photo.
		self::assertSame(27, $summary->longest_break?->length);
		self::assertSame('2025-01-02', $summary->longest_break->from);
		self::assertSame('2025-01-30', $summary->longest_break->to);
	}

	public function testFirstStreakWinsATie(): void
	{
		$summary = StreakCalculator::summarise(['2025-03-01', '2025-03-02', '2025-03-10', '2025-03-11']);

		self::assertSame(2, $summary->longest_daily_streak?->length);
		self::assertSame('2025-03-01', $summary->longest_daily_streak->from);
	}

	public function testWeeklyStreakAcrossYearEnd(): void
	{
		// Weeks starting 2026-12-21, 2026-12-28 (ISO 2026-W53), 2027-01-04; then a gap.
		$summary = StreakCalculator::summarise(['2026-12-21', '2026-12-31', '2027-01-04', '2027-01-25']);

		self::assertSame(3, $summary->longest_weekly_streak?->length);
		self::assertSame('2026-12-21', $summary->longest_weekly_streak->from);
		self::assertSame('2027-01-04', $summary->longest_weekly_streak->to);
	}
}
