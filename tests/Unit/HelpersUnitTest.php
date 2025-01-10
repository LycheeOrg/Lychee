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
		$this->assertEquals('1', Helpers::trancateIf32('10000', 0, 1000)); // check first call => returns 1
		$this->assertEquals('2', Helpers::trancateIf32('10000', 1, 1000)); // check equal => returns +1
		$this->assertEquals('5', Helpers::trancateIf32('50000', 2, 1000)); // check if normal higher => returns higher
	}
}