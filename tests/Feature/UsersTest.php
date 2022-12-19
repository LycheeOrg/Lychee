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

use App\Legacy\AdminAuthentication;
use App\Models\Configs;
use App\Models\User;
use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\ExpectationFailedException;
use function Safe\json_decode;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Tests\AbstractTestCase;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\Feature\Traits\InteractWithSmartAlbums;

class UsersTest extends AbstractTestCase
{
	use InteractWithSmartAlbums;

	public function testSetAdminLoginIfAdminUnconfigured(): void
	{
		/**
		 * because there is no dependency injection in test cases.
		 */
		$sessions_test = new SessionUnitTest($this);

		if (!AdminAuthentication::isAdminNotRegistered()) {
			static::markTestSkipped('Admin user is registered; test skipped.');
		}

		static::assertTrue(AdminAuthentication::loginAsAdminIfNotRegistered());
		$sessions_test->set_admin('lychee', 'password');
		$sessions_test->logout();
		static::assertFalse(AdminAuthentication::isAdminNotRegistered());

		$sessions_test->set_admin('lychee', 'password', 403, 'Admin user is already registered');

		$sessions_test->login('lychee', 'password');
		$sessions_test->logout();

		$sessions_test->login('foo', 'bar', 401);
		$sessions_test->login('lychee', 'bar', 401);
		$sessions_test->login('foo', 'password', 401);
	}

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
		 * 19. try access shared pictures (public)
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
		 * 38. log out
		 */

		// 1
		Auth::loginUsingId(0);

		// 2
		$users_test->add('test_abcd', 'password_abcd', true, true);

		// 3
		$response = $users_test->list();

		// 4
		$content = $response->getContent();
		$this->assertNotFalse($content);
		$t = json_decode($content);
		$id = end($t)->id;
		$response->assertJsonFragment([
			'id' => $id,
			'username' => 'test_abcd',
			'may_upload' => true,
			'is_locked' => true,
		]);

		// 5
		$users_test->add('test_abcd', 'password_abcd', true, true, 409, 'Username already exists');

		// 6
		$users_test->save($id, 'test_abcde', 'password_testing', false, true);

		// 7
		$users_test->add('test_abcd2', 'password_abcd', true, true);
		$response = $users_test->list();
		$content = $response->getContent();
		$this->assertNotFalse($content);
		$t = json_decode($content);
		$id2 = end($t)->id;

		// 8
		$users_test->save($id2, 'test_abcde', 'password_testing', false, true, 409, 'Username already exists');

		// 9
		$sessions_test->logout();

		// 10
		$sessions_test->login('test_abcde', 'password_testing');

		// 11
		$users_test->list(403);

		// 12
		$sessions_test->update_login('test_abcde', 'password_testing2', '', 422, 'The old password field is required.');

		// 13
		$sessions_test->update_login('test_abcde', 'password_testing2', 'password_testing2', 403, 'Insufficient privileges');

		// 14
		$sessions_test->logout();

		// 15
		Auth::loginUsingId(0);

		// 16
		$users_test->save($id, 'test_abcde', 'password_testing', false, false);

		// 17
		$sessions_test->logout();

		// 18
		$sessions_test->login('test_abcde', 'password_testing');
		$sessions_test->init();
		$this->clearCachedSmartAlbums();

		// 19
		$album_tests->get(PublicAlbum::ID, 403);

		// 20
		$album_tests->get(StarredAlbum::ID, 403);

		// 21
		$album_tests->get(UnsortedAlbum::ID, 403);

		// 22
		$album_tests->get(OnThisDayAlbum::ID, 403);

		// 23
		$sessions_test->update_login('test_abcde', 'password_testing2', '', 422, 'The old password field is required.');

		// 24
		$sessions_test->update_login('test_abcde', 'password_testing2', 'password_testing2', 401, 'Previous password is invalid');

		// 25
		$sessions_test->update_login('test_abcd2', 'password_testing2', 'password_testing', 409, 'Username already exists');

		// 26
		$sessions_test->update_login('test_abcdef', 'password_testing2', 'password_testing');

		// 27
		$sessions_test->logout();

		// 28
		$sessions_test->login('test_abcdef', 'password_testing2');

		// 29
		$sessions_test->logout();

		// 30
		Auth::loginUsingId(0);

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
		Auth::loginUsingId(0);

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
		$sessions_test->logout();
		Configs::set('new_photos_notification', $store_new_photos_notification);
	}

	public function testResetToken(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::loginUsingId(0);

		$oldToken = $users_test->reset_token()->offsetGet('token');
		$newToken = $users_test->reset_token()->offsetGet('token');
		self::assertNotEquals($oldToken, $newToken);

		Auth::logout();
	}

	public function testUnsetToken(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::loginUsingId(0);

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
	 * @throws InvalidArgumentException
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
				'is_admin' => false,
				'may_upload' => false,
				'is_locked' => true,
			], ]);

		// update Admin user to non valid rights
		$admin = User::findOrFail(0);
		$admin->may_upload = false;
		$admin->is_locked = true;
		$admin->save();

		// Log as admin and check the rights
		Auth::loginUsingId(0);
		$response = $sessions_test->init();
		$response->assertJsonFragment([
			'rights' => [
				'is_admin' => true,
				'may_upload' => true,
				'is_locked' => false,
			], ]);
		$sessions_test->logout();

		// Correct the rights
		$admin->may_upload = true;
		$admin->is_locked = false;
		$admin->save();

		// Log as admin and verify behaviour
		Auth::loginUsingId(0);
		$response = $sessions_test->init();
		$response->assertJsonFragment([
			'rights' => [
				'is_admin' => true,
				'may_upload' => true,
				'is_locked' => false,
			], ]);
		$sessions_test->logout();
	}

	public function testGetAuthenticatedUser(): void
	{
		$users_test = new UsersUnitTest($this);

		Auth::logout();
		Session::flush();

		$users_test->get_user(204);

		Auth::loginUsingId(0);

		$users_test->get_user(200, [
			'id' => 0,
		]);
	}
}
