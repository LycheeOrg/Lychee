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

class AuthTest extends BaseApiV2Test
{
	public function testGuest(): void
	{
		$response = $this->getJson('Auth::user');
		$this->assertOk($response);
		$response->assertJson([
			'id' => null,
			'has_token' => null,
			'username' => null,
			'email' => null,
		]);

		$response = $this->getJson('Auth::rights');
		$this->assertOk($response);
		$response->assertJson([
			'root_album' => [
				'can_edit' => false,
				'can_upload' => false,
			],
			'settings' => [
				'can_edit' => false,
				'can_see_logs' => false,
				'can_see_diagnostics' => false,
				'can_update' => false,
			],
			'user_management' => [
				'can_create' => false,
				'can_list' => false,
				'can_edit' => false,
				'can_delete' => false,
			],
			'user' => [
				'can_edit' => false,
			],
		]);

		$response = $this->getJson('Auth::config');
		$this->assertOk($response);
		$response->assertJson([
			'oauthProviders' => [],
			'u2f_enabled' => false,
		]);

		$response = $this->postJson('Auth::login', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);
	}

	public function testAuth(): void
	{
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'wrong',
		]);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
		]);
		$this->assertNoContent($response);
	}
}