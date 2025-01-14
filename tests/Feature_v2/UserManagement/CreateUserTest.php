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

namespace Tests\Feature_v2\UserManagement;

use Tests\Feature_v2\Base\BaseApiV2Test;

class CreateUserTest extends BaseApiV2Test
{
	public function testCreateUserGuest(): void
	{
		$response = $this->postJson('UserManagement::create');
		$this->assertUnprocessable($response);

		$response = $this->postJson('UserManagement::create', [
			'username' => 'username',
			'password' => 'password',
			'may_upload' => false,
			'may_edit_own_settings' => false,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('UserManagement::create', [
			'username' => 'username',
			'password' => 'password',
			'may_upload' => false,
			'may_edit_own_settings' => false,
		]);
		$this->assertForbidden($response);
	}

	public function testCreateUserAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('UserManagement::create', [
			'username' => $this->userMayUpload1->username,
			'password' => 'password',
			'may_upload' => false,
			'may_edit_own_settings' => false,
		]);
		$this->assertConflict($response);

		$response = $this->actingAs($this->admin)->postJson('UserManagement::create', [
			'username' => 'newUsername',
			'password' => 'password',
			'may_upload' => false,
			'may_edit_own_settings' => false,
		]);
		$this->assertCreated($response);

		$response = $this->actingAs($this->admin)->getJson('UserManagement');
		$this->assertOk($response);
		$response->assertJsonFragment(
			[
				'username' => 'newUsername',
				'may_administrate' => false,
				'may_upload' => false,
				'may_edit_own_settings' => false,
			]);
	}
}