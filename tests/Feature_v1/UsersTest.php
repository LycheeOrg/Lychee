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

use App\Models\Configs;
use App\Models\User;
use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\ExpectationFailedException;
use function Safe\json_decode;
use Tests\AbstractTestCase;
use Tests\Feature_v1\LibUnitTests\AlbumsUnitTest;
use Tests\Feature_v1\LibUnitTests\SessionUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\InteractWithSmartAlbums;

class UsersTest extends AbstractTestCase
{
	use InteractWithSmartAlbums;

	public function testUsers(): void
	{
		$sessions_test = new SessionUnitTest($this);
		$users_test = new UsersUnitTest($this);
		$album_tests = new AlbumsUnitTest($this);

		/*
		 * Scenario is as follows:
		 *
		 * 1. log as admin
		 * 2. create a user 'test_abcd'
		 * 3. list users
		 * 4. check if the user is found
		 * 5. create a user 'test_abcd' => should return an error: no duplicate username
		 * 6. change username and password of user
		 * 7. create a user 'test_abcd2'
		 * 8. change username to test_abcd => should return an error: no duplicate username
		 * 9. log out
		 * 10. log as 'test_abcde'
		 * 11. try to list users => should fail
		 * 12. change password without old password => should fail (account is locked)
		 * 13. change password => should fail (account is locked)
		 * 14. log out (did not test upload)
		 *
		 * 15. log as admin
		 * 16. unlock account
		 * 17. log out
		 *
		 * 18. log as 'test_abcde'
		 * 20. try access starred pictures
		 * 21. try access recent pictures
		 * 22. try access on_this_day pictures
		 * 23. change password without old password => should fail
		 * 24. change password with wrong password => should fail
		 * 25. change username & password with duplicate username => should fail
		 * 26. change username & password
		 * 27. log out
		 *
		 * 28. log as 'test_abcde'
		 * 29. log out
		 *
		 * 30. log as admin
		 * 31. delete user
		 * 32. log out
		 *
		 * 33. log as admin
		 * 34  get email => should be blank
		 * 35. update email
		 * 36. get email
		 * 37. update email to blank
		 * 38. try to delete yourself (not allowed)
		 * 39. log out
		 */

		// 1
		Auth::loginUsingId(1);

		// 2
		$users_test->add(
			username: 'test_abcd',
			password: 'password_abcd',
			mayUpload: true,
			mayEditOwnSettings: false);

		// 3
		$response = $users_test->list();

		// 4
		$content = $response->getContent();
		self::assertNotFalse($content);
		$t = json_decode($content);
		$id = end($t)->id;
		$response->assertJsonFragment([
			'id' => $id,
			'username' => 'test_abcd',
			'may_upload' => true,
			'may_edit_own_settings' => false,
		]);

		// 5
		$users_test->add(
			username: 'test_abcd',
			password: 'password_abcd',
			mayUpload: true,
			mayEditOwnSettings: false,
			expectedStatusCode: 409,
			assertSee: 'Username already exists');

		// 6
		$users_test->save(
			id: $id,
			username: 'test_abcde',
			password: 'password_testing',
			mayUpload: false,
			mayEditOwnSettings: false);

		// 7
		$users_test->add(
			username: 'test_abcd2',
			password: 'password_abcd',
			mayUpload: true,
			mayEditOwnSettings: false);
		$response = $users_test->list();
		$content = $response->getContent();
		self::assertNotFalse($content);
		$t = json_decode($content);
		$id2 = end($t)->id;

		// 8
		$users_test->save(
			id: $id2,
			username: 'test_abcde',
			password: 'password_testing',
			mayUpload: false,
			mayEditOwnSettings: false,
			expectedStatusCode: 409,
			assertSee: 'Username already exists');

		// 9
		$sessions_test->logout();

		// 10
		$sessions_test->login('test_abcde', 'password_testing');

		// 11
		$users_test->list(403);

		// 12
		$sessions_test->update_login(
			login: 'test_abcde',
			password: 'password_testing2',
			oldPassword: '',
			expectedStatusCode: 422,
			assertSee: 'The old password field is required.');

		// 13
		$sessions_test->update_login(
			login: 'test_abcde',
			password: 'password_testing2',
			oldPassword: 'password_testing2',
			expectedStatusCode: 403,
			assertSee: 'Insufficient privileges');

		// 14
		$sessions_test->logout();

		// 15
		Auth::loginUsingId(1);

		// 16
		$users_test->save(
			id: $id,
			username: 'test_abcde',
			password: 'password_testing',
			mayUpload: false,
			mayEditOwnSettings: true);

		// 17
		$sessions_test->logout();

		// 18
		$sessions_test->login('test_abcde', 'password_testing');
		$sessions_test->init();
		$this->clearCachedSmartAlbums();

		// 20
		$album_tests->get(StarredAlbum::ID, 403);

		// 21
		$album_tests->get(UnsortedAlbum::ID, 403);

		// 22
		$album_tests->get(OnThisDayAlbum::ID, 403);

		// 23
		$sessions_test->update_login(
			login: 'test_abcde',
			password: 'password_testing2',
			oldPassword: '',
			expectedStatusCode: 422,
			assertSee: 'The old password field is required.');

		// 24
		$sessions_test->update_login(
			login: 'test_abcde',
			password: 'password_testing2',
			oldPassword: 'password_testing2',
			expectedStatusCode: 401,
			assertSee: 'Previous password is invalid');

		$user = User::where('username', '=', 'test_abcde')->firstOrFail();
		// 25
		$sessions_test->update_login(
			login: 'test_abcd2',
			password: 'password_testing2',
			oldPassword: 'password_testing',
			expectedStatusCode: 409,
			assertSee: 'Username already exists');

		self::assertEquals($user->password, Auth::user()->password);
		self::assertTrue(Hash::check('password_testing', Auth::user()->password));

		// 26
		$sessions_test->update_login(
			login: 'test_abcdef',
			password: 'password_testing2',
			oldPassword: 'password_testing');

		// 27
		$sessions_test->logout();

		// 28
		$sessions_test->login('test_abcdef', 'password_testing2');

		// 29
		$sessions_test->logout();

		// 30
		Auth::loginUsingId(1);

		// 31
		$users_test->delete($id);
		$users_test->delete($id2);

		// those should fail because we do not touch user of ID 0
		$users_test->delete(0, 422);
		// those should fail because there are no user with id -1
		$users_test->delete(-1, 422);
		$users_test->save(-1, 'toto', 'test', false, true, 422);

		// 32
		$sessions_test->logout();

		// 33
		Auth::loginUsingId(1);

		$configs = Configs::get();
		$store_new_photos_notification = $configs['new_photos_notification'];
		Configs::set('new_photos_notification', '1');

		// 34
		$users_test->get_email();

		// 35
		// Note, this must be a proper email address for an existing mail
		// domain, as the Laravel validator runs a DNS lookup.
		// This means, `void@unexisting.nowhere` though syntactically being
		// correct will trigger an error response.
		$users_test->update_email('legal@support.github.com');

		// 36
		$users_test->get_email();

		// 37
		$users_test->update_email(null);

		// 38
		$response = $users_test->delete(intval(Auth::id()), 403);

		// 39
		$sessions_test->logout();
		Configs::set('new_photos_notification', $store_new_photos_notification);
	}

	public function testResetToken(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::loginUsingId(1);

		$oldToken = $users_test->reset_token()->offsetGet('token');
		$newToken = $users_test->reset_token()->offsetGet('token');
		self::assertNotEquals($oldToken, $newToken);

		Auth::logout();
	}

	public function testUnsetToken(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::loginUsingId(1);

		$oldToken = $users_test->reset_token()->offsetGet('token');
		self::assertNotNull($oldToken);

		$users_test->unset_token();
		$userResponse = $users_test->get_user();
		$userResponse->assertJson([
			'has_token' => false,
		]);

		Auth::logout();
	}

	/**
	 * TODO adapt this test when the admin rights are decoupled from ID = 0.
	 *
	 * @return void
	 *
	 * @throws ExpectationFailedException
	 * @throws \Throwable
	 */
	public function regressionTestAdminAllMighty(): void
	{
		// We first check that the rights are set securely by default
		$sessions_test = new SessionUnitTest($this);
		$response = $sessions_test->init();
		$response->assertJsonFragment([
			'rights' => [
				'can_administrate' => false,
				'can_upload_root' => false,
				'can_edit_own_settings' => true,
			], ]);

		// update Admin user to non valid rights
		$admin = User::query()->findOrFail(1);
		$admin->may_upload = false;
		$admin->may_edit_own_settings = true;
		$admin->save();

		// Log as admin and check the rights
		Auth::loginUsingId(1);
		$response = $sessions_test->init();
		$response->assertJsonFragment([
			'rights' => [
				'can_administrate' => true,
				'can_upload_root' => true,
				'can_edit_own_settings' => false,
			], ]);
		$sessions_test->logout();

		// Correct the rights
		$admin->may_upload = true;
		$admin->may_edit_own_settings = false;
		$admin->save();

		// Log as admin and verify behaviour
		Auth::loginUsingId(1);
		$response = $sessions_test->init();
		$response->assertJsonFragment([
			'rights' => [
				'can_administrate' => true,
				'can_upload_root' => true,
				'can_edit_own_settings' => false,
			], ]);
		$sessions_test->logout();
	}

	public function testGetAuthenticatedUser(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::logout();
		Session::flush();

		$users_test->get_user(401);

		Auth::loginUsingId(1);

		$users_test->get_user(200, [
			'id' => 1,
		]);
	}
}
