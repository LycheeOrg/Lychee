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

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait RequiresEmptyUsers
{
	abstract protected function assertDatabaseCount($table, int $count, $connection = null);

	protected function setUpRequiresEmptyUsers(): void
	{
		// Assert that user table only contains the admin user
		static::assertEquals(
			0,
			DB::table('users')
				->where('id', '>', 1)
				->count()
		);
	}

	protected function tearDownRequiresEmptyUsers(): void
	{
		// Clean up remaining stuff from tests
		DB::table('users')->where('id', '>', 1)->delete();
	}
}
