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

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
	public function testAuthenticateWithToken(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::loginUsingId(0);

		$newToken = $users_test->reset_token()->offsetGet('token');

		Auth::logout();
		Session::flush();

		$response = $this->postJson('/api/User::getAuthenticatedUser');
		$response->assertSee(['test' => '123'], false);
		$response->assertStatus(403);

		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $newToken,
		]);
		$response->assertStatus(200);
		$response->assertSee([
			'id' => 0,
		], false);

		$this->assertAuthenticated();
	}

	public function testAuthenticateWithMultipleToken(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::loginUsingId(0);

		$tokenAdmin = $users_test->reset_token()->offsetGet('token');

		$newUserID = $users_test->add('userMultiTokenTest', 'password')->offsetGet('id');

		Auth::logout();
		Session::flush();

		Auth::loginUsingId($newUserID);

		$tokenUser = $users_test->reset_token()->offsetGet('token');

		Auth::logout();
		Session::flush();

		// try to login as admin
		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $tokenAdmin,
		]);
		$response->assertStatus(200);
		$response->assertSee([
			'id' => 0,
		], false);

		$this->assertAuthenticated();

		// try to login as normal user, *without* logging out first
		$response = $this->postJson('/api/User::getAuthenticatedUser', [], [
			'Authorization' => $tokenUser,
		]);
		$response->assertStatus(200);
		$response->assertSee([
			'username' => 'userMultiTokenTest',
		], false);

		Auth::logout();
		Session::flush();

		Auth::loginUsingId(0);

		$users_test->delete($newUserID);
	}
}
