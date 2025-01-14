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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiV2Test;

class ProfileTest extends BaseApiV2Test
{
	// Test update as guest.
	public function testUpdateLoginGuest(): void
	{
		$response = $this->postJson('Profile::update', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password3',
		]);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password2',
		]);
		$this->assertUnauthorized($response);
	}

	public function testSetEmailGuest(): void
	{
		$response = $this->postJson('Profile::update', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Profile::update', [
			'username' => 'username',
			'email' => 'something@something.com',
			'old_password' => 'passwordpasswordpassword',
		]);
		$this->assertUnauthorized($response);
	}

	public function testUnsetResetTokenGuest(): void
	{
		$response = $this->postJson('Profile::resetToken', []);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Profile::unsetToken', []);
		$this->assertUnauthorized($response);
	}

	public function testUserLocked(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password2',
			'email' => '',
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::update', [
			'username' => 'username',
			'email' => 'something@something.com',
			'old_password' => 'password',
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::resetToken', []);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::unsetToken', []);
		$this->assertForbidden($response);
	}

	public function testUserUnlocked(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password3',
		]);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password2',
			'email' => '',
		]);
		$this->assertCreated($response);

		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		$response = $this->postJson('Auth::login', [
			'username' => 'username',
			'password' => 'password2',
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password2',
			'username' => 'username',
			'email' => 'something@something.com',
		]);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::resetToken', []);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::unsetToken', []);
		$this->assertNoContent($response);
	}
}