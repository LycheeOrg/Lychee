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

use App\Actions\Insights\Helpers\Distribution;
use Tests\AbstractTestCase;

/**
 * Covers Feature 085's exposure summaries (FR-085-13, S-085-12).
 */
class DistributionTest extends AbstractTestCase
{
	public function testEmpty(): void
	{
		$summary = (new Distribution())->summarise();

		self::assertSame([], $summary->values);
		self::assertSame([], $summary->counts);
		self::assertSame(0, $summary->total);
		self::assertSame(0, $summary->excluded);
		self::assertNull($summary->median);
		self::assertNull($summary->mean);
		self::assertNull($summary->mode);
		self::assertNull($summary->min);
		self::assertNull($summary->max);
	}

	public function testValuesAreSortedAndCounted(): void
	{
		$summary = $this->distribution([400, 100, 400, null, 3200, null])->summarise();

		self::assertSame([100.0, 400.0, 3200.0], $summary->values);
		self::assertSame([1, 2, 1], $summary->counts);
		self::assertSame(4, $summary->total);
		self::assertSame(2, $summary->excluded);
		self::assertSame(100.0, $summary->min);
		self::assertSame(3200.0, $summary->max);
		self::assertSame(1025.0, $summary->mean);
		self::assertSame(4100.0, $summary->sum);
	}

	public function testOddMedian(): void
	{
		self::assertSame(2.0, $this->distribution([3, 1, 2])->summarise()->median);
	}

	public function testEvenMedianAveragesTheMiddleValues(): void
	{
		self::assertSame(2.5, $this->distribution([4, 1, 3, 2])->summarise()->median);
	}

	public function testWeightedMedian(): void
	{
		self::assertSame(1.0, $this->distribution([1, 1, 1, 50])->summarise()->median);
	}

	public function testModeTieTakesTheLowestValue(): void
	{
		self::assertSame(2.0, $this->distribution([5, 2, 5, 2, 9])->summarise()->mode);
	}

	public function testCloseFractionsStayApart(): void
	{
		$summary = $this->distribution([1 / 8000, 1 / 10000])->summarise();

		self::assertSame([1, 1], $summary->counts);
	}

	/**
	 * @param array<int,int|float|null> $values
	 */
	private function distribution(array $values): Distribution
	{
		$distribution = new Distribution();
		foreach ($values as $value) {
			$distribution->add($value === null ? null : floatval($value));
		}

		return $distribution;
	}
}
