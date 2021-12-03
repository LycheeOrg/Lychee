<?php

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
		$response = $this->testCase->json('POST', '/api/User::List', []);
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
		$response = $this->testCase->json('POST', '/php/index.php', []);
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
	 * @param string      $upload
	 * @param string      $lock
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function add(
		string $username,
		string $password,
		string $upload,
		string $lock,
		int $expectedStatusCode = 201,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json('POST', '/api/User::Create', [
			'username' => $username,
			'password' => $password,
			'upload' => $upload,
			'lock' => $lock,
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
	 * @param string      $id
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function delete(
		string $id,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json('POST', '/api/User::Delete', [
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
	 * @param string      $id
	 * @param string      $username
	 * @param string      $password
	 * @param string      $upload
	 * @param string      $lock
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function save(
		string $id,
		string $username,
		string $password,
		string $upload,
		string $lock,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json('POST', '/api/User::Save', [
			'id' => $id,
			'username' => $username,
			'password' => $password,
			'upload' => $upload,
			'lock' => $lock,
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
	 * @param string      $email
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse
	 */
	public function update_email(
		string $email,
		int $expectedStatusCode = 204,
		?string $assertSee = null
	): TestResponse {
		$response = $this->testCase->json('POST', '/api/User::UpdateEmail', [
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
		$response = $this->testCase->json('POST', '/api/User::GetEmail');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
