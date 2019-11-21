<?php

namespace Tests\Feature\Lib;

use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class UsersUnitTest
{
	/**
	 * List users.
	 *
	 * @return TestResponse
	 */
	public function list(
		TestCase &$testCase,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/User::List', []);
		$response->assertStatus(200);
		if ($result != 'true') {
			$response->assertSee($result);
		}

		return $response;
	}

	/**
	 * @return TestResponse
	 */
	public function init(
		TestCase &$testCase,
		string $result = 'true'
	) {
		$response = $testCase->post('/php/index.php', []);
		$response->assertStatus(200);
		if ($result != 'true') {
			$response->assertSee($result);
		}

		return $response;
	}

	/**
	 * Add a new user.
	 */
	public function add(
		TestCase &$testCase,
		string $username,
		string $password,
		string $upload,
		string $lock,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/User::Create', [
			'username' => $username,
			'password' => $password,
			'upload' => $upload,
			'lock' => $lock,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Delete a user.
	 */
	public function delete(
		TestCase &$testCase,
		string $id,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/User::Delete', [
			'id' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}

	/**
	 * Save modifications to a user.
	 */
	public function save(
		TestCase &$testCase,
		string $id,
		string $username,
		string $password,
		string $upload,
		string $lock,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/User::Save', [
			'id' => $id,
			'username' => $username,
			'password' => $password,
			'upload' => $upload,
			'lock' => $lock,
		]);
		$response->assertStatus(200);
		$response->assertSee($result);
	}
}