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

namespace Tests\Unit\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Feature 115: FR-115-14, S-115-39 (`lychee:create_user` half).
 */
class CreateUserCommandTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testFirstUserGetsFullImportAccessOthersNone(): void
	{
		self::assertSame(0, User::query()->count(), 'the test database starts without users');

		$this->artisan('lychee:create_user', ['username' => 'first', 'password' => 'password'])->assertExitCode(0);
		$this->artisan('lychee:create_user', ['username' => 'second', 'password' => 'password', '--may-administrate' => true])->assertExitCode(0);

		$first = User::query()->where('username', '=', 'first')->firstOrFail();
		$second = User::query()->where('username', '=', 'second')->firstOrFail();
		self::assertTrue($first->may_administrate);
		self::assertSame('/', $first->server_path);
		self::assertTrue($second->may_administrate);
		self::assertNull($second->server_path);
	}
}
