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

namespace Tests\Unit\Models;

use App\Enum\LicenseType;
use App\Models\Album;
use Tests\AbstractTestCase;

class AlbumTest extends AbstractTestCase
{
	public function testHeader(): void
	{
		$a = new Album();
		self::assertNull($a->header);
	}

	public function testLicense(): void
	{
		$a = new Album();
		$a->license = LicenseType::CC0;
		self::assertEquals(LicenseType::CC0, $a->license);
	}

	public function testAspectRatio(): void
	{
		$a = new Album();
		$a->performDeleteOnModel();
		self::assertTrue(true); // checking that the thing above does not produce an exception
	}
}
