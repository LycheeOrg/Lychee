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

namespace Tests\Unit\Jobs;

use App\Jobs\CheckTreeState;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class CheckTreeStateTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testReturnsCachedValueWithoutRecomputing(): void
	{
		Cache::shouldReceive('remember')
			->once()
			->with('tree_state', 24 * 3600, \Mockery::type(\Closure::class))
			->andReturn(['oddness' => 3]);

		$result = (new CheckTreeState())->handle();

		self::assertEquals(['oddness' => 3], $result);
	}

	public function testComputesErrorsOnCacheMiss(): void
	{
		// The album tree is empty in the test DB, so this exercises the real
		// (DB-free of any fixture data) counting query without needing mocks.
		Cache::forget('tree_state');

		$result = (new CheckTreeState())->handle();

		self::assertIsArray($result);
	}
}
