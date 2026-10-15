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

use App\Services\BucketKeys;
use Illuminate\Support\Carbon;
use Tests\AbstractTestCase;

/**
 * Covers {@see BucketKeys}: the row-intrinsic keys stored on `photos`
 * (FR-092-01, FR-092-02) and the `created_at` key (FR-092-03).
 */
class BucketKeysTest extends AbstractTestCase
{
	public function testTakenKeyOfCastValueKeepsItsZone(): void
	{
		$taken_at = Carbon::create(2026, 7, 1, 16, 5, 0, 'Europe/Paris');

		self::assertSame('2026-07-01-16', BucketKeys::takenKey($taken_at));
	}

	public function testTakenKeyOfNullIsNull(): void
	{
		self::assertNull(BucketKeys::takenKey(null));
	}

	public function testTakenKeyFromRawConvertsUtcToNamedZone(): void
	{
		self::assertSame('2026-07-01-16', BucketKeys::takenKeyFromRaw('2026-07-01 14:05:00', 'Europe/Paris'));
		self::assertSame('2026-01-15-15', BucketKeys::takenKeyFromRaw('2026-01-15 14:05:00.123456', 'Europe/Paris'));
	}

	public function testTakenKeyFromRawConvertsUtcToFixedOffset(): void
	{
		self::assertSame('2026-07-02-01', BucketKeys::takenKeyFromRaw('2026-07-01 23:30:00', '+02:00'));
		self::assertSame('2026-06-30-21', BucketKeys::takenKeyFromRaw('2026-07-01 02:30:00', '-05:00'));
	}

	public function testTakenKeyFromRawAcrossDaylightSavingChange(): void
	{
		// 2026-03-29: Europe/Paris switches from UTC+1 to UTC+2 at 01:00 UTC.
		self::assertSame('2026-03-29-01', BucketKeys::takenKeyFromRaw('2026-03-29 00:30:00', 'Europe/Paris'));
		self::assertSame('2026-03-29-03', BucketKeys::takenKeyFromRaw('2026-03-29 01:30:00', 'Europe/Paris'));
	}

	public function testTakenKeyFromRawWithoutZoneStaysUtc(): void
	{
		self::assertSame('2026-07-01-14', BucketKeys::takenKeyFromRaw('2026-07-01 14:05:00', null));
		self::assertSame('2026-07-01-14', BucketKeys::takenKeyFromRaw('2026-07-01 14:05:00', ''));
	}

	public function testTakenKeyFromRawOfNullIsNull(): void
	{
		self::assertNull(BucketKeys::takenKeyFromRaw(null, 'Europe/Paris'));
	}

	public function testCreatedKeyIsTheStoredValueToTheHour(): void
	{
		self::assertSame('2026-07-01-14', BucketKeys::createdKey('2026-07-01 14:05:00'));
		self::assertSame('2026-07-01-09', BucketKeys::createdKey('2026-07-01 09:59:59.999999'));
	}

	public function testTitleDateKeyReadsLeadingDate(): void
	{
		self::assertSame('2024-01-01', BucketKeys::titleDateKey('2024 Trip'));
		self::assertSame('2024-03-01', BucketKeys::titleDateKey('2024-03 Trip'));
		self::assertSame('2024-03-15', BucketKeys::titleDateKey('2024-03-15 Trip'));
		self::assertSame('2024-03-15', BucketKeys::titleDateKey('  2024-03-15'));
		self::assertSame('2024-02-29', BucketKeys::titleDateKey('2024-02-29'));
	}

	public function testTitleDateKeyReadsCompactLeadingDate(): void
	{
		self::assertSame('2024-03-15', BucketKeys::titleDateKey('20240315 Trip'));
		self::assertSame('2024-03-15', BucketKeys::titleDateKey('20240315_123456'));
		self::assertSame('2024-03-15', BucketKeys::titleDateKey('20240315123456'));
		self::assertSame('2024-01-01', BucketKeys::titleDateKey('202403 x'));
		self::assertSame('2024-01-01', BucketKeys::titleDateKey('2024123 x'));
	}

	public function testTitleDateKeyOfTitleWithoutLeadingDateIsNull(): void
	{
		self::assertNull(BucketKeys::titleDateKey('Holiday 2024'));
		self::assertNull(BucketKeys::titleDateKey(''));
		self::assertNull(BucketKeys::titleDateKey('IMG_2024-03-15'));
	}

	public function testTitleDateKeyOfInvalidCalendarDateIsNull(): void
	{
		self::assertNull(BucketKeys::titleDateKey('2024-13-45 x'));
		self::assertNull(BucketKeys::titleDateKey('2023-02-29'));
		self::assertNull(BucketKeys::titleDateKey('0000 x'));
		self::assertNull(BucketKeys::titleDateKey('20241345 x'));
		self::assertNull(BucketKeys::titleDateKey('20230229'));
	}
}
