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

use App\DTO\Version;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use Tests\AbstractTestCase;

class VersionDTOUnitTest extends AbstractTestCase
{
	/**
	 * Lychee version are constrained between 0 and 999999.
	 *
	 * @return void
	 */
	public function testInvalidVersionNumber(): void
	{
		$this->expectException(LycheeInvalidArgumentException::class);
		Version::createFromInt(1000000);
	}

	/**
	 * Lychee version in string must be max of 6 characters.
	 *
	 * @return void
	 */
	public function testInvalidVersionString(): void
	{
		$this->expectException(LycheeInvalidArgumentException::class);
		Version::createFromString('1000000');
	}

	/**
	 * Lychee version in string must be max of 6 characters.
	 *
	 * @return void
	 */
	public function testValidVersionString(): void
	{
		$version = Version::createFromString('40306');
		self::assertEquals(4, $version->major);
		self::assertEquals(3, $version->minor);
		self::assertEquals(6, $version->patch);
	}
}