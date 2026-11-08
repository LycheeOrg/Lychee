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

	/**
	 * Feature 112, S-112-07 – the console unban lets the IP log in again at once.
	 */
	public function testUnbanFromConsoleLetsTheIpLogInAgain(): void
	{
		Cache::flush();
		$credentials = ['username' => 'username', 'password' => 'wrong_password'];
		for ($i = 0; $i < 10; $i++) {
			$this->postJson('Auth::login', $credentials)->assertStatus(401);
		}
		$this->postJson('Auth::login', $credentials)->assertStatus(429);

		$this->artisan('lychee:blocked-ips', ['--unban' => '127.0.0.1'])
			->expectsOutput('Unbanned 127.0.0.1.')
			->assertExitCode(0);

		$this->postJson('Auth::login', $credentials)->assertStatus(401);
		Cache::flush();
	}
}