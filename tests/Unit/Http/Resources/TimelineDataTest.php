<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\Models\Utils\TimelineData;
use Tests\AbstractTestCase;

class TimelineDataTest extends AbstractTestCase
{
	public function testParseDateFromTitleWithFullDate(): void
	{
		$result = TimelineData::parseDateFromTitle('2023-12-25 Christmas Day');

		self::assertNotNull($result);
		self::assertEquals(2023, $result->year);
		self::assertEquals(12, $result->month);
		self::assertEquals(25, $result->day);
	}

	public function testParseDateFromTitleWithYearAndMonth(): void
	{
		$result = TimelineData::parseDateFromTitle('2023-12 December');

		self::assertNotNull($result);
		self::assertEquals(2023, $result->year);
		self::assertEquals(12, $result->month);
		self::assertEquals(1, $result->day); // Should default to day 1
	}

	public function testParseDateFromTitleWithYearOnly(): void
	{
		$result = TimelineData::parseDateFromTitle('2023 A Great Year');

		self::assertNotNull($result);
		self::assertEquals(2023, $result->year);
		self::assertEquals(1, $result->month); // Should default to month 1
		self::assertEquals(1, $result->day); // Should default to day 1
	}

	public function testParseDateFromTitleWithNoText(): void
	{
		$result = TimelineData::parseDateFromTitle('2023-06-15');

		self::assertNotNull($result);
		self::assertEquals(2023, $result->year);
		self::assertEquals(6, $result->month);
		self::assertEquals(15, $result->day);
	}

	public function testParseDateFromTitleWithInvalidFormat(): void
	{
		$result = TimelineData::parseDateFromTitle('December 25, 2023');

		self::assertNull($result);
	}

	public function testParseDateFromTitleWithNoDate(): void
	{
		$result = TimelineData::parseDateFromTitle('Just a regular title');

		self::assertNull($result);
	}

	public function testParseDateFromTitleWithEmptyString(): void
	{
		$result = TimelineData::parseDateFromTitle('');

		self::assertNull($result);
	}

	public function testParseDateFromTitleWithDateInMiddle(): void
	{
		// Should not match - date must be at the beginning
		$result = TimelineData::parseDateFromTitle('Some text 2023-12-25 more text');

		self::assertNull($result);
	}

	public function testParseDateFromTitleWithLeadingZeros(): void
	{
		$result = TimelineData::parseDateFromTitle('2023-01-05 New Year');

		self::assertNotNull($result);
		self::assertEquals(2023, $result->year);
		self::assertEquals(1, $result->month);
		self::assertEquals(5, $result->day);
	}

	public function testParseDateFromTitleWithInvalidMonth(): void
	{
		// Invalid month (13) - Carbon will overflow to next year
		$result = TimelineData::parseDateFromTitle('2023-13-01 Invalid Month');

		self::assertNotNull($result);
		// Month 13 overflows to January of 2024
		self::assertEquals(2024, $result->year);
		self::assertEquals(1, $result->month);
		self::assertEquals(1, $result->day);
	}

	public function testParseDateFromTitleWithInvalidDay(): void
	{
		// Invalid day (32) - Carbon will overflow to next month
		$result = TimelineData::parseDateFromTitle('2023-12-32 Invalid Day');

		self::assertNotNull($result);
		// Day 32 of December overflows to January 1 of 2024
		self::assertEquals(2024, $result->year);
		self::assertEquals(1, $result->month);
		self::assertEquals(1, $result->day);
	}

	public function testParseDateFromTitleWithShortYear(): void
	{
		// Year must be 4 digits
		$result = TimelineData::parseDateFromTitle('23-12-25 Short Year');

		self::assertNull($result);
	}

	public function testParseDateFromTitleWithExtraHyphens(): void
	{
		$result = TimelineData::parseDateFromTitle('2023-12-25-something');

		self::assertNotNull($result);
		self::assertEquals(2023, $result->year);
		self::assertEquals(12, $result->month);
		self::assertEquals(25, $result->day);
	}

	public function testParseDateFromTitleWithWhitespace(): void
	{
		$result = TimelineData::parseDateFromTitle('2023-12-25 	Multiple   Spaces');

		self::assertNotNull($result);
		self::assertEquals(2023, $result->year);
		self::assertEquals(12, $result->month);
		self::assertEquals(25, $result->day);
	}

	public function testParseDateFromTitleWithLeapYear(): void
	{
		$result = TimelineData::parseDateFromTitle('2024-02-29 Leap Day');

		self::assertNotNull($result);
		self::assertEquals(2024, $result->year);
		self::assertEquals(2, $result->month);
		self::assertEquals(29, $result->day);
	}

	public function testParseDateFromTitleWithHistoricalDate(): void
	{
		$result = TimelineData::parseDateFromTitle('1900-01-01 Turn of Century');

		self::assertNotNull($result);
		self::assertEquals(1900, $result->year);
		self::assertEquals(1, $result->month);
		self::assertEquals(1, $result->day);
	}

	public function testParseDateFromTitleWithFutureDate(): void
	{
		$result = TimelineData::parseDateFromTitle('2099-12-31 Future Date');

		self::assertNotNull($result);
		self::assertEquals(2099, $result->year);
		self::assertEquals(12, $result->month);
		self::assertEquals(31, $result->day);
	}
}
