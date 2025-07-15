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

use Illuminate\Support\Facades\Cache;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class LoginTest extends BaseApiWithDataTest
{
	public function testLoginRateMiddleware(): void
	{
		Cache::flush();
		for ($i = 0; $i < 10; $i++) {
			$response = $this->postJson('Auth::login', [
				'username' => 'username',
				'password' => 'wrong_password',
			]);
			$response->assertStatus(401);
		}

		$response = $this->postJson('Auth::login', [
			'username' => 'username',
			'password' => 'wrong_password',
		]);
		$response->assertStatus(429);
		Cache::flush();
	}
}