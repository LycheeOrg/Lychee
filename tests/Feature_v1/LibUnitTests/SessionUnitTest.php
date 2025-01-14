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

namespace Tests\Feature_v1\LibUnitTests;

use Illuminate\Testing\TestResponse;
use Tests\AbstractTestCase;
use Tests\Traits\CatchFailures;

class SessionUnitTest
{
	use CatchFailures;

	private AbstractTestCase $testCase;

	public function __construct(AbstractTestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	/**
	 * Logging in.
	 *
	 * @param string      $username
	 * @param string      $password
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function login(
		string $username,
		string $password,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Session::login', [
			'username' => $username,
			'password' => $password,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function init(
		int $expectedStatusCode = 200,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Session::init');
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Logging out.
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function logout(): TestResponse
	{
		$response = $this->testCase->postJson('/api/Session::logout');
		$response->assertSuccessful();

		return $response;
	}

	/**
	 * Set a new login and password.
	 *
	 * @param string      $login
	 * @param string      $password
	 * @param string      $oldPassword
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function update_login(
		string $login,
		string $password,
		string $oldPassword,
		int $expectedStatusCode = 200,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::updateLogin', [
			'username' => $login,
			'password' => $password,
			'oldPassword' => $oldPassword,
		]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
