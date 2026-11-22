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

namespace Tests\Unit\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\AbstractTestCase;

/**
 * Feature 115: FR-115-01, FR-115-14, S-115-38.
 */
class ServerPathMigrationTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function migration(): object
	{
		return require base_path('database/migrations/2026_11_15_000001_add_server_path_to_users.php');
	}

	public function testColumnExists(): void
	{
		self::assertTrue(Schema::hasColumn('users', 'server_path'));
	}

	public function testOwnerGetsFullAccess(): void
	{
		$owner = User::factory()->may_administrate()->create();
		$other_admin = User::factory()->may_administrate()->create();
		$user = User::factory()->may_upload()->create();
		DB::table('users')->update(['server_path' => null]);
		DB::table('configs')->where('key', '=', 'owner_id')->update(['value' => strval($owner->id)]);

		$this->migration()->grantOwner();

		self::assertSame('/', $owner->fresh()->server_path);
		self::assertNull($other_admin->fresh()->server_path);
		self::assertNull($user->fresh()->server_path);
	}

	public function testNoOwnerIsNoOp(): void
	{
		User::factory()->may_administrate()->create();
		DB::table('users')->update(['server_path' => null]);
		DB::table('configs')->where('key', '=', 'owner_id')->update(['value' => '0']);

		$this->migration()->grantOwner();

		self::assertSame(0, DB::table('users')->whereNotNull('server_path')->count());
	}
}
