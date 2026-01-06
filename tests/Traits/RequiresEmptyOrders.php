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

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait RequiresEmptyOrders
{
	abstract protected function assertDatabaseCount($table, int $count, $connection = null);

	protected function setUpRequiresEmptyOrders(): void
	{
		static::assertEquals(0, DB::table('order_items')->count());
		static::assertEquals(0, DB::table('orders')->count());
	}

	protected function tearDownRequiresEmptyOrders(): void
	{
		// Clean up remaining stuff from tests
		DB::table('order_items')->delete();
		DB::table('orders')->delete();
	}
}
