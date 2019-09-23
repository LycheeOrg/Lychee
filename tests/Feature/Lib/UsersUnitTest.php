<?php

namespace Tests\Feature\Lib;

use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class UsersUnitTest
{
	/**
	 * List users.
	 *
	 * @param TestCase $testCase
	 * @param string   $result
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
	 * @param TestCase $testCase
	 * @param string   $result
	 *
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
	 *
	 * @param TestCase $testCase
	 * @param string   $username
	 * @param string   $password
	 * @param string   $upload
	 * @param string   $lock
	 * @param string   $result
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
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $result
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
	 *
	 * @param TestCase $testCase
	 * @param string   $id
	 * @param string   $username
	 * @param string   $password
	 * @param string   $upload
	 * @param string   $lock
	 * @param string   $result
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