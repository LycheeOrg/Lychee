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

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class InviteUserTest extends BaseApiWithDataTest
{
	public function testInviteUsersGuest(): void
	{
		$response = $this->getJson('UserManagement::invite');
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->getJson('UserManagement::invite');
		$this->assertForbidden($response);
	}

	public function testInviteUsersAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('UserManagement::invite');
		$this->assertOk($response);
		$api_url = URL::temporarySignedRoute('register-api', now()->addDays(7));
		$api_url_parts = explode('?', $api_url);
		$url = route('register') . '?' . $api_url_parts[1];

		$response->assertJson(
			[
				'invitation_link' => $url,
				'valid_for' => 7,
			]);

		Auth::logout();
		Configs::set('user_registration_enabled', false);

		$response = $this->putJson('Profile?' . $api_url_parts[1], [
			'username' => 'newUser',
			'email' => 'test@example.com',
			'password' => 'password',
			'password_confirmation' => 'password',
		]);
		$response->assertCreated();

		Configs::set('user_registration_enabled', false);
	}
}