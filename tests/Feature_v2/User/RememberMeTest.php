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

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RememberMeTest extends BaseApiWithDataTest
{
	public function testLoginWithRememberMeTrue(): void
	{
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
			'remember_me' => true,
		]);
		$this->assertNoContent($response);

		// Verify a remember cookie is set.
		// The Lychee guard is named 'lychee', so the cookie is 'remember_lychee_*' (not 'remember_web_*').
		$has_remember_cookie = false;
		foreach ($response->headers->getCookies() as $cookie) {
			if (str_starts_with($cookie->getName(), 'remember_lychee_')) {
				$has_remember_cookie = true;
				break;
			}
		}
		$this->assertTrue($has_remember_cookie, 'Expected a remember_lychee_* cookie to be set');
	}

	public function testLoginWithRememberMeFalse(): void
	{
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
			'remember_me' => false,
		]);
		$this->assertNoContent($response);

		// Verify no remember cookie is set
		foreach ($response->headers->getCookies() as $cookie) {
			$this->assertFalse(
				str_starts_with($cookie->getName(), 'remember_lychee_'),
				'Did not expect a remember_lychee_* cookie'
			);
		}
	}

	public function testLoginWithoutRememberMeField(): void
	{
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
		]);
		$this->assertNoContent($response);

		// Verify no remember cookie is set (backward compatibility)
		foreach ($response->headers->getCookies() as $cookie) {
			$this->assertFalse(
				str_starts_with($cookie->getName(), 'remember_lychee_'),
				'Did not expect a remember_lychee_* cookie when remember_me is absent'
			);
		}
	}

	public function testLoginWithInvalidCredentialsAndRememberMe(): void
	{
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'wrong_password',
			'remember_me' => true,
		]);
		$this->assertUnauthorized($response);

		// Verify no remember cookie is set on failed auth
		foreach ($response->headers->getCookies() as $cookie) {
			$this->assertFalse(
				str_starts_with($cookie->getName(), 'remember_lychee_'),
				'Did not expect a remember_lychee_* cookie on failed login'
			);
		}
	}

	public function testLoginWithNonBooleanRememberMe(): void
	{
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
			'remember_me' => 'not_a_boolean',
		]);
		$this->assertUnprocessable($response);
	}
}
