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
		self::assertEquals(null, Helpers::decimalToDegreeMinutesSeconds(null, true));
		self::assertEquals(null, Helpers::decimalToDegreeMinutesSeconds(null, false));
		self::assertEquals("45° 30' 0\" N", Helpers::decimalToDegreeMinutesSeconds(45.5, true));
		self::assertEquals("12° 34' 29\" W", Helpers::decimalToDegreeMinutesSeconds(-12.575, false));
	}

	public function testGetSymbolByQuantity(): void
	{
		self::assertEquals('0 B', Helpers::getSymbolByQuantity(0));
		self::assertEquals('0 B', Helpers::getSymbolByQuantity(-10));
		self::assertEquals('512.00 B', Helpers::getSymbolByQuantity(512));
		self::assertEquals('1.00 KB', Helpers::getSymbolByQuantity(1024));
		self::assertEquals('1.00 MB', Helpers::getSymbolByQuantity(1024 * 1024));
		self::assertEquals('1.00 GB', Helpers::getSymbolByQuantity(1024 * 1024 * 1024));
		self::assertEquals('1.50 KB', Helpers::getSymbolByQuantity(1536));
		self::assertEquals('2.25 MB', Helpers::getSymbolByQuantity(2.25 * 1024 * 1024));
	}

	public function testHasPermissions(): void
	{
		self::assertEquals(false, Helpers::hasPermissions('does-not-exist'));
		// Test with existing readable/writable directory (storage path should exist)
		$storagePath = storage_path();
		if (file_exists($storagePath)) {
			self::assertEquals(true, Helpers::hasPermissions($storagePath));
		}
	}

	public function testSecondsToHMS(): void
	{
		self::assertEquals('0s', Helpers::secondsToHMS(0));
		self::assertEquals('1s', Helpers::secondsToHMS(1));
		self::assertEquals('59s', Helpers::secondsToHMS(59));
		self::assertEquals('1m', Helpers::secondsToHMS(60));
		self::assertEquals('1m30s', Helpers::secondsToHMS(90));
		self::assertEquals('2m', Helpers::secondsToHMS(120));
		self::assertEquals('1h', Helpers::secondsToHMS(3600));
		self::assertEquals('1h30m', Helpers::secondsToHMS(5400));
		self::assertEquals('1h1s', Helpers::secondsToHMS(3601));
		self::assertEquals('2h30m45s', Helpers::secondsToHMS(9045));
		self::assertEquals('1h', Helpers::secondsToHMS(3600.5)); // Test float
	}

	public function testCensor(): void
	{
		self::assertEquals('', Helpers::censor(''));
		self::assertEquals('**c', Helpers::censor('abc'));
		self::assertEquals('te****st', Helpers::censor('testtest'));
		self::assertEquals('he*****rld', Helpers::censor('helloworld'));
		self::assertEquals('pa****rd', Helpers::censor('password'));
		// Test with different censoring percentage
		self::assertEquals('p******d', Helpers::censor('password', 0.25));
		self::assertEquals('pas**ord', Helpers::censor('password', 0.75));
	}

	public function testIsExecAvailable(): void
	{
		// This test will return true or false depending on the system configuration
		$result = Helpers::isExecAvailable();
		self::assertIsBool($result);
	}
}