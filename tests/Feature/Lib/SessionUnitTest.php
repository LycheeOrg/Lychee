<?php

namespace Tests\Feature\Lib;

use App\ModelFunctions\SessionFunctions;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class SessionUnitTest
{
	/**
	 * Logging in.
	 */
	public function login(
		TestCase &$testCase,
		string $username,
		string $password,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Session::login', [
			'user' => $username,
			'password' => $password,
		]);
		$response->assertOk();
		$response->assertSee($result);
	}

	/**
	 * @return TestResponse
	 */
	public function init(
		TestCase &$testCase,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Session::init', []);
		$response->assertStatus(200);
		if ($result != 'true') {
			$response->assertSee($result);
		}

		return $response;
	}

	/**
	 * Logging out.
	 */
	public function logout(TestCase &$testCase)
	{
		$response = $testCase->post('/api/Session::logout');
		$response->assertOk();
		$response->assertSee('true');
	}

	/**
	 * Set a new login and password.
	 */
	public function set_new(
		TestCase &$testCase,
		string $login,
		string $password,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Settings::setLogin', [
			'username' => $login,
			'password' => $password,
		]);
		$response->assertOk();
		$response->assertSee($result);
	}

	/**
	 * Set a new login and password.
	 */
	public function set_old(
		TestCase &$testCase,
		string $login,
		string $password,
		string $oldUsername,
		string $oldPassword,
		string $result = 'true'
	) {
		$response = $testCase->post('/api/Settings::setLogin', [
			'username' => $login,
			'password' => $password,
			'oldUsername' => $oldUsername,
			'oldPassword' => $oldPassword,
		]);
		$response->assertOk();
		$response->assertSee($result);
	}

	public function log_as_id(int $id)
	{
		$sessionFunctions = new SessionFunctions();
		$sessionFunctions->log_as_id($id);
	}
}