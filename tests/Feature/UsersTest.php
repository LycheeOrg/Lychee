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

use App\Facades\AccessControl;
use App\ModelFunctions\SessionFunctions;
use App\Models\Configs;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\TestCase;

class UsersTest extends TestCase
{
	public function testSetLogin(): void
	{
		/**
		 * because there is no dependency injection in test cases.
		 */
		$sessionFunctions = new SessionFunctions();
		$sessions_test = new SessionUnitTest($this);

		$clear = false;
		$configs = Configs::get();

		/*
		 * Check if password and username are set
		 */
		if ($configs['password'] == '' && $configs['username'] == '') {
			$clear = true;

			$sessions_test->set_new('lychee', 'password');
			$sessions_test->logout();

			$sessions_test->login('lychee', 'password');
			$sessions_test->logout();
		} else {
			static::markTestSkipped('Username and Password are set. We do not bother testing further.');
		}

		/*
		 * We check that there are username and password set in the database
		 */
		static::assertFalse($sessionFunctions->noLogin());

		$sessions_test->login('foo', 'bar', 401);
		$sessions_test->login('lychee', 'bar', 401);
		$sessions_test->login('foo', 'password', 401);

		/*
		 * If we did set login and password we clear them
		 */
		if ($clear) {
			Configs::set('username', '');
			Configs::set('password', '');
		}
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
		 * 22. change password without old password => should fail
		 * 23. change password with wrong password => should fail
		 * 24. change username & password with duplicate username => should fail
		 * 25. change username & password
		 * 26. log out
		 *
		 * 27. log as 'test_abcde'
		 * 28. log out
		 *
		 * 29. log as admin
		 * 30. delete user
		 * 31. log out
		 *
		 * 32. log as admin
		 * 33  get email => should be blank
		 * 34. update email
		 * 35. get email
		 * 36  update email to blank
		 * 37. log out
		 */

		// 1
		AccessControl::log_as_id(0);

		// 2
		$users_test->add('test_abcd', 'test_abcd', true, true);

		// 3
		$response = $users_test->list();

		// 4
		$t = json_decode($response->getContent());
		$id = end($t)->id;
		$response->assertJsonFragment([
			'id' => $id,
			'username' => 'test_abcd',
			'may_upload' => true,
			'is_locked' => true,
		]);

		// 5
		$users_test->add('test_abcd', 'test_abcd', true, true, 409, 'Username already exists');

		// 6
		$users_test->save($id, 'test_abcde', 'testing', false, true);

		// 7
		$users_test->add('test_abcd2', 'test_abcd', true, true);
		$response = $users_test->list();
		$t = json_decode($response->getContent());
		$id2 = end($t)->id;

		// 8
		$users_test->save($id2, 'test_abcde', 'testing', false, true, 409, 'Username already exists');

		// 9
		$sessions_test->logout();

		// 10
		$sessions_test->login('test_abcde', 'testing');

		// 11
		$users_test->list(403);

		// 12
		$sessions_test->set_new('test_abcde', 'testing2', 403, 'Account is locked');

		// 13
		$sessions_test->set_old('test_abcde', 'testing2', 'test_abcde', 'testing2', 403, 'Account is locked');

		// 14
		$sessions_test->logout();

		// 15
		AccessControl::log_as_id(0);

		// 16
		$users_test->save($id, 'test_abcde', 'testing', false, false);

		// 17
		$sessions_test->logout();

		// 18
		$sessions_test->login('test_abcde', 'testing');
		$sessions_test->init();

		// 19
		$album_tests->get('public', 403);

		// 20
		$album_tests->get('starred', 403);

		// 21
		$album_tests->get('unsorted', 403);

		// 22
		$sessions_test->set_new('test_abcde', 'testing2', 401, 'Previous username or password are invalid');

		// 23
		$sessions_test->set_old('test_abcde', 'testing2', 'test_abcde', 'testing2', 401, 'Previous username or password are invalid');

		// 24
		$sessions_test->set_old('test_abcd2', 'testing2', 'test_abcde', 'testing2', 409, 'Username already exists');

		// 25
		$sessions_test->set_old('test_abcdef', 'testing2', 'test_abcde', 'testing');

		// 26
		$sessions_test->logout();

		// 27
		$sessions_test->login('test_abcdef', 'testing2');

		// 28
		$sessions_test->logout();

		// 29
		AccessControl::log_as_id(0);

		// 30
		$users_test->delete($id);
		$users_test->delete($id2);

		// those should fail because we do not touch user of ID 0
		$users_test->delete('0', 422);
		// those should fail because there are no user with id -1
		$users_test->delete('-1', 422);
		$users_test->save('-1', 'toto', 'test', false, true, 422);

		// 31
		$sessions_test->logout();

		// 32
		AccessControl::log_as_id(0);

		$configs = Configs::get();
		$store_new_photos_notification = $configs['new_photos_notification'];
		Configs::set('new_photos_notification', '1');

		// 33
		$users_test->get_email();

		// 34
		// Note, this must be a proper email address for an existing mail
		// domain, as the Laravel validator runs a DNS lookup.
		// This means, `void@unexisting.nowhere` though syntactically being
		// correct will trigger an error response.
		$users_test->update_email('legal@support.github.com');

		// 35
		$users_test->get_email();

		// 36
		$users_test->update_email(null);

		// 37
		$sessions_test->logout();
		Configs::set('new_photos_notification', $store_new_photos_notification);
	}
}
