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

namespace Tests\Unit\Models;

use App\Models\Statistics;
use Tests\AbstractTestCase;

class StatisticsTest extends AbstractTestCase
{
	public function testRatingAvgIsZeroWithoutRatings(): void
	{
		$stats = new Statistics(['rating_sum' => 0, 'rating_count' => 0]);
		self::assertEquals(0, $stats->rating_avg);
	}

	public function testRatingAvgIsRoundedToTwoDecimals(): void
	{
		$stats = new Statistics(['rating_sum' => 10, 'rating_count' => 3]);
		self::assertEquals(3.33, $stats->rating_avg);
	}
}
