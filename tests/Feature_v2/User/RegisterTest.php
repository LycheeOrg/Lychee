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

namespace Tests\Feature_v2\User;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RegisterTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set('user_registration_enabled', '1');
		Configs::invalidateCache();
	}

	public function tearDown(): void
	{
		Configs::set('user_registration_enabled', '1');
		Configs::invalidateCache();
		parent::tearDown();
	}

	public function testRegistrationRequiresValidData()
	{
		$response = $this->putJson('/Profile', []);
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['username', 'email', 'password']);
	}

	public function testRegistrationFailsWithDuplicateUsername()
	{
		$response = $this->putJson('/Profile', [
			'username' => $this->userMayUpload1->username,
			'email' => 'newuser@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);
		$this->assertStatus($response, 409);
	}

	public function testRegistrationSucceedsWithValidInput()
	{
		$response = $this->putJson('/Profile', [
			'username' => 'newuser',
			'email' => 'newuser@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertCreated($response);
		$response->assertJson(['message' => 'User registered successfully']);
		$this->assertDatabaseHas('users', [
			'username' => 'newuser',
			'email' => 'newuser@example.com',
		]);
	}

	public function testRegistrationFailsWithInvalidEmail()
	{
		$response = $this->putJson('/Profile', [
			'username' => 'testuser',
			'email' => 'invalid-email',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['email']);
	}

	public function testRegistrationForbiddenWhenLoggedIn()
	{
		$this->actingAs($this->userMayUpload1);
		$response = $this->putJson('/Profile', [
			'username' => 'anotheruser',
			'email' => 'newuser@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);
		$this->assertForbidden($response);
	}

	public function testRegistrationForbiddenWhenDisabled()
	{
		Configs::set('user_registration_enabled', '0');
		Configs::invalidateCache();

		$response = $this->putJson('/Profile', [
			'username' => 'anotheruser',
			'email' => 'newuser@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);
		$this->assertUnauthorized($response);
	}
}
