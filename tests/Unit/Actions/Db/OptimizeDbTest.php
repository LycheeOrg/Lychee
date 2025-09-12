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

namespace Tests\Unit\Actions\Db;

use App\Actions\Db\OptimizeDb;
use Tests\AbstractTestCase;

class OptimizeDbTest extends AbstractTestCase
{
	/**
	 * Iterate over the directories and check if the files contain the correct license and copyright info..
	 *
	 * @return void
	 */
	public function testOptimizeDb(): void
	{
		$optimize = new OptimizeDb();
		$output = count($optimize->do());
		self::assertTrue(in_array($output, [3, 34], true), 'OptimizeDb should return either 3 or 30: ' . $output);
	}
}
