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

use App\Models\Configs;
use App\Models\User;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 091 – registration when the email may stand in for the username
 * (FR-091-07 … FR-091-09).
 */
class RegistrationEmailLoginTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set('user_registration_enabled', '1');
	}

	public function tearDown(): void
	{
		Configs::set('email_login_enabled', '0');
		Configs::set('user_registration_enabled', '0');

		parent::tearDown();
	}

	/**
	 * S-091-09 – no username given, so the email becomes the username.
	 */
	public function testRegistrationWithoutUsernameUsesTheEmail(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->putJson('/Profile', [
			'email' => 'no-username@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertCreated($response);
		$this->assertDatabaseHas('users', [
			'username' => 'no-username@example.com',
			'email' => 'no-username@example.com',
		]);
	}

	/**
	 * S-091-09 – an empty username is handled like a missing one, because the
	 * global `ConvertEmptyStringsToNull` middleware turns it into `null`.
	 */
	public function testRegistrationWithEmptyUsernameUsesTheEmail(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->putJson('/Profile', [
			'username' => '',
			'email' => 'empty-username@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertCreated($response);
		$this->assertDatabaseHas('users', [
			'username' => 'empty-username@example.com',
			'email' => 'empty-username@example.com',
		]);
	}

	/**
	 * S-091-10 – an explicit username is kept.
	 */
	public function testRegistrationWithUsernameKeepsIt(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->putJson('/Profile', [
			'username' => 'explicit-name',
			'email' => 'explicit-name@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertCreated($response);
		$this->assertDatabaseHas('users', [
			'username' => 'explicit-name',
			'email' => 'explicit-name@example.com',
		]);
	}

	/**
	 * S-091-11 – with the setting off, the username stays mandatory.
	 */
	public function testRegistrationWithoutUsernameIsRejectedWhenSettingOff(): void
	{
		Configs::set('email_login_enabled', '0');

		$response = $this->putJson('/Profile', [
			'email' => 'no-username@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['username']);
	}

	/**
	 * S-091-17 – an email longer than the `users.email`/`users.username` column
	 * is rejected on the email field, so a mirrored username can never exceed
	 * `UsernameRule`'s limit.
	 */
	public function testRegistrationRejectsAnOverlongEmail(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->putJson('/Profile', [
			'email' => str_repeat('a', 90) . '@example.com', // 102 characters
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors(['email']);
	}

	/**
	 * S-091-18 – with the setting on, an email that doubles as another
	 * account's username is refused: that identifier would address two
	 * accounts.
	 */
	public function testRegistrationRejectsAnEmailUsedAsAnotherUsername(): void
	{
		Configs::set('email_login_enabled', '1');

		User::factory()->create(['username' => 'ambiguous@example.com', 'email' => null]);

		$response = $this->putJson('/Profile', [
			'username' => 'a-free-username',
			'email' => 'ambiguous@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertStatus($response, 409);
	}

	/**
	 * S-091-18 – with the setting off the two attributes are independent, so
	 * the same payload is accepted.
	 */
	public function testRegistrationAllowsAnEmailUsedAsAnotherUsernameWhenSettingOff(): void
	{
		Configs::set('email_login_enabled', '0');

		User::factory()->create(['username' => 'ambiguous@example.com', 'email' => null]);

		$response = $this->putJson('/Profile', [
			'username' => 'a-free-username',
			'email' => 'ambiguous@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertCreated($response);
	}

	/**
	 * S-091-12 – the email cannot take a username which is already in use.
	 */
	public function testRegistrationWithoutUsernameRejectsATakenIdentifier(): void
	{
		Configs::set('email_login_enabled', '1');

		$taken = User::factory()->create(['username' => 'taken@example.com', 'email' => null]);

		$response = $this->putJson('/Profile', [
			'email' => $taken->username,
			'password' => 'password123',
			'password_confirmation' => 'password123',
		]);

		$this->assertStatus($response, 409);
	}
}
