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

namespace Tests\Feature_v2\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ApiTokenTest extends BaseApiWithDataTest
{
	public function testAuthenticateWithTokenOnly(): void
	{
		$token = $this->resetToken($this->admin);

		$response = $this->getJson('Auth::user');
		$this->assertOk($response);

		$response = $this->getJson('Auth::user', headers: ['Authorization' => $token]);
		$this->assertOk($response);
		$response->assertJson([
			'id' => $this->admin->id,
			'username' => $this->admin->username,
			'email' => $this->admin->email,
		]);

		$this->assertAuthenticated();
	}

	/**
	 * Ensures that changing authentication via token while re-using the
	 * same session is forbidden.
	 *
	 * @return void
	 */
	public function testForbiddenChangeOfTokensInSameSession(): void
	{
		$adminToken = $this->resetToken($this->admin);
		$userToken = $this->resetToken($this->userMayUpload1);

		// perform a request as admin
		$response = $this->getJson('Auth::user', headers: ['Authorization' => $adminToken]);
		$this->assertStatus($response, 200);
		$response->assertJson(['id' => $this->admin->id]);

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
		$response = $this->getJson('Auth::user', headers: ['Authorization' => $userToken]);
		$this->assertStatus($response, 200);
		$response->assertJson(['id' => $this->userMayUpload1->id]);
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
		$adminToken = $this->resetToken($this->admin);

		// Do some request authenticated by token
		$response = $this->getJson('Auth::user', headers: ['Authorization' => $adminToken]);
		$this->assertStatus($response, 200);
		$response->assertJson(['id' => $this->admin->id]);

		// We need to call this to mimic the behaviour of real-world
		// requests.
		// Normally, the service classes incl. guards are re-created for
		// each request.
		// In contrast to `testChangeOfTokensWithoutSession` we do not
		// re-create the whole application, because we want to keep the
		// session.
		Auth::forgetGuards();

		// Login stateful
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
		], [
			'Authorization' => $adminToken,
		]);
		$this->assertStatus($response, 204);

		Auth::forgetGuards();

		// Ensure that we are still logged in even without providing the token
		$response = $this->getJson('Auth::user');
		$this->assertStatus($response, 200);
		$response->assertJson(['id' => $this->admin->id]);

		Auth::forgetGuards();

		// Ensure that we can log out (stateful) while still providing the token
		$response = $this->postJson('Auth::logout', headers: ['Authorization' => $adminToken]);
		$this->assertStatus($response, 204);

		Auth::forgetGuards();

		// Ensure that we are logged out without the token
		$response = $this->getJson('Auth::user');
		$this->assertStatus($response, 200);
		$response->assertJson(['id' => null]);
	}

	/**
	 * Ensures that logging in with another user than the given token refers
	 * to does not work.
	 *
	 * @return void
	 */
	public function testLoginWithDifferentUserThanToken(): void
	{
		$user2Token = $this->resetToken($this->userMayUpload2);

		$response = $this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->username,
			'password' => 'password',
		], [
			'Authorization' => $user2Token,
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
		$user2Token = $this->resetToken($this->userMayUpload2);

		// Login normally and stateful without a token
		$response = $this->postJson('Auth::login', [
			'username' => $this->userMayUpload1->username,
			'password' => 'password',
		]);
		$this->assertStatus($response, 204);

		$response = $this->getJson('Auth::user');
		$this->assertStatus($response, 200);
		$response->assertJson(['id' => $this->userMayUpload1->id]);

		// We need to call this to mimic the behaviour of real-world
		// requests.
		// Normally, the service classes incl. guards are re-created for
		// each request.
		// In contrast to `testChangeOfTokensWithoutSession` we do not
		// re-create the whole application, because we want to keep the
		// session.
		Auth::forgetGuards();

		// Do some request and provide wrong token
		$response = $this->getJson('Auth::user', headers: ['Authorization' => $user2Token]);
		$this->assertStatus($response, 400);
	}

	/**
	 * Resets the token of the given user and returns it.
	 *
	 * @param User $user the user to reset the token for
	 *
	 * @return string the token
	 */
	protected function resetToken(User $user): string
	{
		$response = $this->actingAs($user)->postJson('Profile::resetToken', []);
		$this->assertCreated($response);
		Auth::logout();
		Session::flush();

		return $response->json('token');
	}
}