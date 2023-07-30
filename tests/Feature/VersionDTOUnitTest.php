<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Data\Version;
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
		$this->assertEquals(4, $version->major);
		$this->assertEquals(3, $version->minor);
		$this->assertEquals(6, $version->patch);
	}
}