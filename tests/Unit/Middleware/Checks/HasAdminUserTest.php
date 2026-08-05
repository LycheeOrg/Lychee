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

use App\Http\Middleware\Checks\HasAdminUser;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\AbstractTestCase;

class HasAdminUserTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testReturnsTrueWhenColumnMissing(): void
	{
		Schema::shouldReceive('hasColumn')->once()->with('users', 'may_administrate')->andReturn(false);

		$check = new HasAdminUser();
		self::assertTrue($check->assert());
	}

	public function testReturnsFalseWhenNoAdminUserExists(): void
	{
		User::factory()->create(['may_administrate' => false]);

		$check = new HasAdminUser();
		self::assertFalse($check->assert());
	}

	public function testReturnsTrueWhenAdminUserExists(): void
	{
		User::factory()->may_administrate()->create();

		$check = new HasAdminUser();
		self::assertTrue($check->assert());
	}
}
