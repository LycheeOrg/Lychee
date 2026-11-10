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

use App\Actions\Insights\Helpers\MilestoneFinder;
use Tests\AbstractTestCase;

/**
 * Covers the dates on which a running photo count reaches a milestone
 * (Feature 085, FR-085-19).
 */
class MilestoneFinderTest extends AbstractTestCase
{
	public function testDayReachingEachThreshold(): void
	{
		$milestones = MilestoneFinder::dates(['2020-01-01' => 40, '2020-02-01' => 59, '2020-03-01' => 1, '2020-04-01' => 900], [100, 1000]);

		self::assertSame([100 => '2020-03-01', 1000 => '2020-04-01'], $milestones);
	}

	public function testUnreachedThresholdsAreLeftOut(): void
	{
		self::assertSame([], MilestoneFinder::dates(['2020-01-01' => 99], [100]));
	}

	public function testOneDayCanReachSeveralThresholds(): void
	{
		self::assertSame([100 => '2020-01-01', 1000 => '2020-01-01'], MilestoneFinder::dates(['2020-01-01' => 1500], [100, 1000]));
	}
}
