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

	/**
	 * Feature 118: FR-118-21, S-118-13.
	 */
	public function testShareAndEditOptions(): void
	{
		$this->artisan('lychee:create_user', ['username' => 'first', 'password' => 'password'])->assertExitCode(0);
		$this->artisan('lychee:create_user', ['username' => 'sharer', 'password' => 'password', '--may-share' => true])->assertExitCode(0);
		$this->artisan('lychee:create_user', ['username' => 'editor', 'password' => 'password', '--may-edit' => true])->assertExitCode(0);
		$this->artisan('lychee:create_user', ['username' => 'uploader', 'password' => 'password', '--may-upload' => true])->assertExitCode(0);
		$this->artisan('lychee:create_user', ['username' => 'nobody', 'password' => 'password'])->assertExitCode(0);

		$rights = fn (string $name) => User::query()->where('username', '=', $name)->firstOrFail()->only(['may_upload', 'may_share', 'may_edit']);
		self::assertSame(['may_upload' => false, 'may_share' => true, 'may_edit' => false], $rights('sharer'));
		self::assertSame(['may_upload' => false, 'may_share' => false, 'may_edit' => true], $rights('editor'));
		self::assertSame(['may_upload' => true, 'may_share' => true, 'may_edit' => true], $rights('uploader'));
		self::assertSame(['may_upload' => false, 'may_share' => false, 'may_edit' => false], $rights('nobody'));
	}
}
