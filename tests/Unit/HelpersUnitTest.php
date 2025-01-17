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

namespace Tests\Unit;

use App\Exceptions\Internal\ZeroModuloException;
use App\Facades\Helpers;
use Tests\AbstractTestCase;

class HelpersUnitTest extends AbstractTestCase
{
	/**
	 * Testing truncate.
	 *
	 * This code is only used in migrations, there are no code path that hits it otherwise
	 *
	 * @return void
	 */
	public function testTrancateIf32(): void
	{
		self::assertEquals('1', Helpers::trancateIf32('10000', 0, 1000)); // check first call => returns 1
		self::assertEquals('2', Helpers::trancateIf32('10000', 1, 1000)); // check equal => returns +1
		self::assertEquals('5', Helpers::trancateIf32('50000', 2, 1000)); // check if normal higher => returns higher
		self::assertEquals('50000', Helpers::trancateIf32('50000', 2, 2147483649)); // check if normal higher => returns higher
	}

	public function testHasFullPermissions(): void
	{
		self::assertEquals(false, Helpers::hasFullPermissions('does-not-exists'));
	}

	public function testGcd(): void
	{
		self::assertEquals(5, Helpers::gcd(10, 5));
		self::assertEquals(1, Helpers::gcd(7, 5));
	}

	public function testGcdException(): void
	{
		self::expectException(ZeroModuloException::class);
		Helpers::gcd(0, 0);
	}

	public function testConvertSize(): void
	{
		self::assertEquals(1, Helpers::convertSize('1'));
		self::assertEquals(1024, Helpers::convertSize('1K'));
		self::assertEquals(1024 * 1024, Helpers::convertSize('1M'));
		self::assertEquals(1024 * 1024 * 1024, Helpers::convertSize('1G'));
	}

	public function testDecimalToDegreeMinutesSeconds(): void
	{
		self::assertEquals('', Helpers::decimalToDegreeMinutesSeconds(190, true));
		self::assertEquals('', Helpers::decimalToDegreeMinutesSeconds(190, false));
		self::assertEquals("90° 0' 0\" N", Helpers::decimalToDegreeMinutesSeconds(90, true));
		self::assertEquals("90° 0' 0\" S", Helpers::decimalToDegreeMinutesSeconds(-90, true));
		self::assertEquals("90° 0' 0\" E", Helpers::decimalToDegreeMinutesSeconds(90, false));
		self::assertEquals("90° 0' 0\" W", Helpers::decimalToDegreeMinutesSeconds(-90, false));
	}
}