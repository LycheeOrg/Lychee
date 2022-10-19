<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UsersUnitTest
{
	private TestCase $testCase;

	public function __construct(TestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	/**
	 * List users.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function list(
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::list');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function init(
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/php/index.php');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Add a new user.
	 *
	 * @param string      $username
	 * @param string      $password
	 * @param bool        $mayUpload
	 * @param bool        $isLocked
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function add(
		string $username,
		string $password,
		bool $mayUpload = true,
		bool $isLocked = false,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::create', [
			'username' => $username,
			'password' => $password,
			'may_upload' => $mayUpload,
			'is_locked' => $isLocked,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
	 * @return TestResponse
	 */
	public function delete(
		int $id,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::delete', [
			'id' => $id,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
	 * @param bool        $isLocked
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function save(
		int $id,
		string $username,
		string $password,
		bool $mayUpload = true,
		bool $isLocked = false,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::save', [
			'id' => $id,
			'username' => $username,
			'password' => $password,
			'may_upload' => $mayUpload,
			'is_locked' => $isLocked,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
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
	 * @return TestResponse
	 */
	public function update_email(
		?string $email,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::setEmail', [
			'email' => $email,
		]);
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Get the email of a user.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function get_email(
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::getEmail');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Retrieve currentUser.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function get_user(
		int $expectedStatusCode = 200,
		string|array|null $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::getAuthenticatedUser');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * reset Token of a user.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function reset_token(
		int $expectedStatusCode = 200,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::resetToken');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * Disable Token of a user.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function unset_token(
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/User::unsetToken');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
