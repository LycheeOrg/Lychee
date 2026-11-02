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

namespace Tests\Unit\Services;

use App\Services\MonthDayKey;
use Illuminate\Support\Carbon;
use Tests\AbstractTestCase;

/**
 * Covers {@see MonthDayKey}: `photos.month_day_key` (FR-106-01).
 */
class MonthDayKeyTest extends AbstractTestCase
{
	public function testTakenDateInItsOwnZone(): void
	{
		// 2 November 00:30 in Paris, stored as 1 November 23:30 UTC.
		$taken_at = Carbon::create(2026, 11, 2, 0, 30, 0, 'Europe/Paris');
		$created_at = Carbon::create(2026, 5, 5, 12, 0, 0);

		self::assertSame(1102, MonthDayKey::of($taken_at, $created_at));
	}

	public function testUploadDateWithoutTakenDate(): void
	{
		self::assertSame(307, MonthDayKey::of(null, Carbon::create(2024, 3, 7, 23, 59, 59)));
	}

	public function testLeapDay(): void
	{
		self::assertSame(229, MonthDayKey::of(Carbon::create(2024, 2, 29, 8, 0, 0), Carbon::create(2026, 1, 1)));
	}

	public function testLastDayOfTheYear(): void
	{
		self::assertSame(1231, MonthDayKey::of(Carbon::create(2025, 12, 31, 23, 0, 0), Carbon::create(2026, 1, 1)));
	}
}
