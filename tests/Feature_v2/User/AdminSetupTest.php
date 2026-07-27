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

namespace Tests\Feature_v2\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\AbstractTestCase;

class AdminSetupTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testCreatesAdminSuccessfully(): void
	{
		$response = $this->postJson('/api/v2/Admin::Setup', [
			'username' => 'first-admin',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertCreated($response);
		$response->assertJson(['message' => 'Admin account created successfully']);
		$this->assertDatabaseHas('users', [
			'username' => 'first-admin',
			'may_administrate' => true,
		]);
		$this->assertAuthenticated();

		$user = User::query()->where('username', '=', 'first-admin')->firstOrFail();
		$owner_id = DB::table('configs')->where('key', '=', 'owner_id')->value('value');
		$this->assertEquals($user->id, $owner_id);
	}

	public function testRequiresValidData(): void
	{
		$response = $this->postJson('/api/v2/Admin::Setup', []);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['username', 'password']);
	}

	public function testForbiddenWhenAdminAlreadyExists(): void
	{
		$existing = new User();
		$existing->username = 'existing-admin';
		$existing->password = Hash::make('irrelevant');
		$existing->may_upload = true;
		$existing->may_edit_own_settings = true;
		$existing->may_administrate = true;
		$existing->save();

		$response = $this->postJson('/api/v2/Admin::Setup', [
			'username' => 'second-admin',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertForbidden($response);
	}

	public function testConflictWhenUsernameAlreadyTaken(): void
	{
		$existing = new User();
		$existing->username = 'taken-username';
		$existing->password = Hash::make('irrelevant');
		$existing->may_upload = false;
		$existing->may_edit_own_settings = false;
		$existing->may_administrate = false;
		$existing->save();

		$response = $this->postJson('/api/v2/Admin::Setup', [
			'username' => 'taken-username',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertConflict($response);
	}
}
