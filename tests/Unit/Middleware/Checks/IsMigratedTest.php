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

namespace Tests\Unit\Middleware\Checks;

use App\DTO\Version;
use App\Http\Middleware\Checks\IsMigrated;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\InstalledVersion;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class IsMigratedTest extends AbstractTestCase
{
	public function testReturnsTrueWhenVersionsMatch(): void
	{
		$this->mock(InstalledVersion::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getVersion')->andReturn(new Version(6, 4, 2));
		});
		$this->mock(FileVersion::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getVersion')->andReturn(new Version(6, 4, 2));
		});

		$check = new IsMigrated();
		self::assertTrue($check->assert());
	}

	public function testReturnsFalseWhenVersionsDiffer(): void
	{
		$this->mock(InstalledVersion::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getVersion')->andReturn(new Version(6, 4, 1));
		});
		$this->mock(FileVersion::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getVersion')->andReturn(new Version(6, 4, 2));
		});

		$check = new IsMigrated();
		self::assertFalse($check->assert());
	}
}
