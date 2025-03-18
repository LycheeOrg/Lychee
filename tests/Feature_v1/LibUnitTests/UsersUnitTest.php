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

class UsersUnitTest
{
	use CatchFailures;

	private AbstractTestCase $testCase;

	public function __construct(AbstractTestCase $test_case)
	{
		$this->testCase = $test_case;
	}

	/**
	 * List users.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function list(
		int $expected_status_code = 200,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Users::list');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
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
		int $expected_status_code = 200,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/php/index.php');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * Add a new user.
	 *
	 * @param string      $username
	 * @param string      $password
	 * @param bool        $mayUpload
	 * @param bool        $mayEditOwnSettings
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function add(
		string $username,
		string $password,
		bool $may_upload = true,
		bool $may_edit_own_settings = true,
		int $expected_status_code = 201,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Users::create', [
			'username' => $username,
			'password' => $password,
			'may_upload' => $may_upload,
			'may_edit_own_settings' => $may_edit_own_settings,
		]);
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * Delete a user.
	 *
	 * @param int         $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function delete(
		int $id,
		int $expected_status_code = 204,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Users::delete', [
			'id' => $id,
		]);
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * Save modifications to a user.
	 *
	 * @param int         $id
	 * @param string      $username
	 * @param string      $password
	 * @param bool        $mayUpload
	 * @param bool        $mayEditOwnSettings
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function save(
		int $id,
		string $username,
		string $password,
		bool $may_upload = true,
		bool $may_edit_own_settings = true,
		int $expected_status_code = 204,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Users::save', [
			'id' => $id,
			'username' => $username,
			'password' => $password,
			'may_upload' => $may_upload,
			'may_edit_own_settings' => $may_edit_own_settings,
		]);
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * Update email on user.
	 *
	 * @param string|null $email
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function update_email(
		?string $email,
		int $expected_status_code = 204,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::setEmail', [
			'email' => $email,
		]);
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * Get the email of a user.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function get_email(
		int $expected_status_code = 200,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::getAuthenticatedUser');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * Retrieve currentUser.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function get_user(
		int $expected_status_code = 200,
		string|array|null $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::getAuthenticatedUser');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * reset Token of a user.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function reset_token(
		int $expected_status_code = 200,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::resetToken');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}

	/**
	 * Disable Token of a user.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function unset_token(
		int $expected_status_code = 204,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::unsetToken');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}
}
