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

namespace Tests\Feature_v1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\RequiresEmptyUsers;

class ApiTokenTest extends AbstractTestCase
{
	use RequiresEmptyUsers;

	protected UsersUnitTest $users_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->users_tests = new UsersUnitTest($this);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	public function testAuthenticateWithTokenOnly(): void
	{
		$token = $this->resetAdminToken();

		$response = $this->postJson('/api/User::getAuthenticatedUser');
		$this->assertStatus($response, 401);

		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $token,
		]);
		$this->assertStatus($response, 200);
		$response->assertSee([
			'id' => 1,
		], false);

		$this->assertAuthenticated();
	}

	/**
	 * Ensures that stateless authentication with tokens is possible, if session
	 * is not re-used.
	 *
	 * @return void
	 */
	public function testChangeOfTokensWithoutSession(): void
	{
		$adminToken = $this->resetAdminToken();
		list($userId, $userToken) = $this->createUserWithToken('userMultiTokenTest', 'password');

		// perform a request as admin
		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $adminToken,
		]);
		$this->assertStatus($response, 200);
		$response->assertSee([
			'id' => 1,
		], false);

		// We need to call this to mimic the behaviour of real-world
		// requests and re-create the session and also destroy all
		// resolved service classes.
		// Note, this is not exactly what we want to test.
		// Refreshing the application entirely destroys the session.
		// But actually, we only want to perform another, independent request
		// below without sending the session cookie, but the session should
		// still be available on the backend.
		// TODO: Improve this when https://github.com/laravel/framework/discussions/44088 has been answered.
		$this->refreshApplication();

		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $userToken,
		]);
		$this->assertStatus($response, 200);
		$response->assertSee([
			'id' => $userId,
		], false);
	}

	/**
	 * Ensures that changing authentication via token while re-using the
	 * same session is forbidden.
	 *
	 * @return void
	 */
	public function testForbiddenChangeOfTokensInSameSession(): void
	{
		$adminToken = $this->resetAdminToken();
		list(, $userToken) = $this->createUserWithToken('userMultiTokenTest', 'password');

		// perform a request as admin
		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $adminToken,
		]);
		$this->assertStatus($response, 200);
		$response->assertSee([
			'id' => 1,
		], false);

		// We need to call this to mimic the behaviour of real-world
		// requests.
		// Normally, the service classes incl. guards are re-created for
		// each request.
		// In contrast to `testChangeOfTokensWithoutSession` we do not
		// re-create the whole application, because we want to keep the
		// session.
		Auth::forgetGuards();

		// Attempt to perform a request as another user, recycling the same
		// session which has previously been created by the first request.
		// Note: By some Laravel magic, subsequent request inside the same
		// test method "magically" share the same session without doing
		// anything explicitly about it.
		// See: https://github.com/laravel/framework/discussions/44088.
		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $userToken,
		]);
		$this->assertStatus($response, 401);
	}

	/**
	 * Ensures that (stateful) login still works if token is additionally
	 * provided, and that login remains valid even without token.
	 *
	 * Do the same for the logout.
	 *
	 * @return void
	 */
	public function testLoginAndLogoutWithToken(): void
	{
		list($userId, $userToken) = $this->createUserWithToken('user', 'pwd');

		// Do some request authenticated by token
		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $userToken,
		]);
		$this->assertStatus($response, 200);
		$response->assertSee([
			'id' => $userId,
		], false);

		// We need to call this to mimic the behaviour of real-world
		// requests.
		// Normally, the service classes incl. guards are re-created for
		// each request.
		// In contrast to `testChangeOfTokensWithoutSession` we do not
		// re-create the whole application, because we want to keep the
		// session.
		Auth::forgetGuards();

		// Login stateful
		$response = $this->postJson('/api/Session::login', [
			'username' => 'user',
			'password' => 'pwd',
		], [
			'Authorization' => $userToken,
		]);
		$this->assertStatus($response, 204);

		Auth::forgetGuards();

		// Ensure that we are still logged in even without providing the token
		$response = $this->postJson('/api/User::getAuthenticatedUser');
		$this->assertStatus($response, 200);
		$response->assertSee([
			'id' => $userId,
		], false);

		Auth::forgetGuards();

		// Ensure that we can log out (stateful) while still providing the token
		$response = $this->postJson('/api/Session::logout', [], [
			'Authorization' => $userToken,
		]);
		$this->assertStatus($response, 204);

		Auth::forgetGuards();

		// Ensure that we are logged out without the token
		$response = $this->postJson('/api/User::getAuthenticatedUser');
		$this->assertStatus($response, 401);
	}

	/**
	 * Ensures that logging in with another user than the given token refers
	 * to does not work.
	 *
	 * @return void
	 */
	public function testLoginWithDifferentUserThanToken(): void
	{
		$this->createUserWithToken('user1', 'pwd1');
		list(, $userToken2) = $this->createUserWithToken('user2', 'pwd2');

		$response = $this->postJson('/api/Session::login', [
			'username' => 'user1',
			'password' => 'pwd1',
		], [
			'Authorization' => $userToken2,
		]);
		$this->assertStatus($response, 400);
	}

	/**
	 * Ensures that using a token which belongs to a different user than
	 * the one which has been previously logged in leads to an error.
	 *
	 * @return void
	 */
	public function testProvideDifferentTokenThanLogin(): void
	{
		list($userId1) = $this->createUserWithToken('user1', 'pwd1');
		list(, $userToken2) = $this->createUserWithToken('user2', 'pwd2');

		// Login normally and stateful without a token
		$response = $this->postJson('/api/Session::login', [
			'username' => 'user1',
			'password' => 'pwd1',
		]);
		$this->assertStatus($response, 204);
		$response = $this->postJson('/api/User::getAuthenticatedUser');
		$this->assertStatus($response, 200);
		$response->assertSee([
			'id' => $userId1,
		], false);

		// We need to call this to mimic the behaviour of real-world
		// requests.
		// Normally, the service classes incl. guards are re-created for
		// each request.
		// In contrast to `testChangeOfTokensWithoutSession` we do not
		// re-create the whole application, because we want to keep the
		// session.
		Auth::forgetGuards();

		// Do some request and provide wrong token
		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $userToken2,
		]);
		$this->assertStatus($response, 400);
	}

	/**
	 * @return string the Admin token
	 */
	protected function resetAdminToken(): string
	{
		Auth::loginUsingId(1);
		$token = $this->users_tests->reset_token()->offsetGet('token');
		Auth::logout();
		Session::flush();

		return $token;
	}

	/**
	 * @param string $userName
	 * @param string $password
	 *
	 * @return array{0: int, 1: string} ID and token of new user
	 */
	protected function createUserWithToken(string $userName, string $password): array
	{
		Auth::loginUsingId(1);
		$id = $this->users_tests->add($userName, $password)->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($id);
		$token = $this->users_tests->reset_token()->offsetGet('token');
		Auth::logout();
		Session::flush();

		return [$id, $token];
	}
}
