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

namespace Tests\Feature_v3\User;

use App\Models\Configs;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 091 – updating a username or email must not reuse another
 * account's identifier while email login is enabled.
 */
class ProfileEmailLoginTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set('allow_username_change', '1');
		// Factory usernames are person names; email-shaped ones let either
		// attribute be submitted as the other.
		$this->userMayUpload1->username = 'self@example.com';
		$this->userMayUpload1->save();
		$this->userMayUpload2->username = 'other@example.com';
		$this->userMayUpload2->email = 'taken@example.com';
		$this->userMayUpload2->save();
	}

	public function tearDown(): void
	{
		Configs::set('email_login_enabled', '0');

		parent::tearDown();
	}

	public function testProfileUsernameCannotReuseAnotherEmail(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'taken@example.com',
			'email' => null,
		]);
		$this->assertConflict($response);
	}

	public function testProfileEmailCannotReuseAnotherUsername(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => $this->userMayUpload1->username,
			'email' => $this->userMayUpload2->username,
		]);
		$this->assertConflict($response);
	}

	public function testProfileEmailMayEqualOwnUsername(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => $this->userMayUpload1->username,
			'email' => $this->userMayUpload1->username,
		]);
		$this->assertCreated($response);
	}

	public function testOverlapIsAllowedWhenEmailLoginIsDisabled(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'taken@example.com',
			'email' => null,
		]);
		$this->assertCreated($response);
	}

	public function testAdminCannotRenameUserToAnotherEmail(): void
	{
		Configs::set('email_login_enabled', '1');

		$response = $this->actingAs($this->admin)->patchJson('UserManagement', [
			'id' => $this->userMayUpload1->id,
			'username' => 'taken@example.com',
			'may_upload' => true,
			'may_edit_own_settings' => true,
		]);
		$this->assertConflict($response);
	}
}
