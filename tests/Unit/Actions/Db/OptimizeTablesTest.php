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

namespace Tests\Unit\Actions\Db;

use App\Actions\Db\OptimizeTables;
use Tests\AbstractTestCase;

class OptimizeTablesTest extends AbstractTestCase
{
	/**
	 * Iterate over the directories and check if the files contain the correct license and copyright info..
	 *
	 * @return void
	 */
	public function testOptimizeTables(): void
	{
		$optimize = new OptimizeTables();
		$output = count($optimize->do());
		self::assertTrue(in_array($output, [3, 37, 38], true), 'OptimizeTables should return either 3 or 37 or 38: ' . $output);
	}
}
