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

namespace Tests\Unit\DTO;

use App\DTO\Insights\InsightsPeriod;
use Tests\AbstractTestCase;

/**
 * Covers the UTC bounds of an Insights period (Feature 085 ADR-085-03,
 * Feature 108 NFR-108-02, S-108-08).
 */
class InsightsPeriodTest extends AbstractTestCase
{
	public function testYearWindowIsWidenedByFourteenHours(): void
	{
		self::assertSame(['2024-12-31 10:00:00', '2026-01-01 13:59:59'], InsightsPeriod::year(2025)->utcWindow());
	}

	public function testYearCoreIsNarrowedByFourteenHours(): void
	{
		self::assertSame(['2025-01-01 14:00:00', '2025-12-31 09:59:59'], InsightsPeriod::year(2025)->utcCore());
	}

	public function testOneDayRangeHasAnInvertedCore(): void
	{
		$core = InsightsPeriod::range('2025-01-01', '2025-01-01')->utcCore();

		self::assertSame(['2025-01-01 14:00:00', '2025-01-01 09:59:59'], $core);
	}

	public function testTwoDayRangeHasAShortCore(): void
	{
		self::assertSame(['2025-01-01 14:00:00', '2025-01-02 09:59:59'], InsightsPeriod::range('2025-01-01', '2025-01-02')->utcCore());
	}

	public function testLibraryHasNoBounds(): void
	{
		self::assertNull(InsightsPeriod::library()->utcWindow());
		self::assertNull(InsightsPeriod::library()->utcCore());
	}
}
