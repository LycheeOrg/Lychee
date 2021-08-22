<?php

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
	public function testSetLogin()
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
			$this->markTestSkipped('Username and Password are set. We do not bother testing further.');
		}

		/*
		 * We check that there are username and password set in the database
		 */
		$this->assertFalse($sessionFunctions->noLogin());

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

	public function testUsers()
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
		$users_test->add('test_abcd', 'test_abcd', '1', '1');

		// 3
		$response = $users_test->list();

		// 4
		$t = json_decode($response->getContent());
		$id = end($t)->id;
		$response->assertJsonFragment([
			'id' => $id,
			'username' => 'test_abcd',
			'upload' => 1,
			'lock' => 1,
		]);

		// 5
		// TODO: Fix this on the server.side. The expected status code should be '409' (Conflict), not 200 (OK).
		$users_test->add('test_abcd', 'test_abcd', '1', '1', 200, 'Error: username must be unique');

		// 6
		$users_test->save($id, 'test_abcde', 'testing', '0', '1');

		// 7
		$users_test->add('test_abcd2', 'test_abcd', '1', '1');
		$response = $users_test->list();
		$t = json_decode($response->getContent());
		$id2 = end($t)->id;

		// 8
		// TODO: Fix this on the server.side. The expected status code should be '409' (Conflict), not 200 (OK).
		$users_test->save($id2, 'test_abcde', 'testing', '0', '1', 200, 'Error: username must be unique');

		// 9
		$sessions_test->logout();

		// 10
		$sessions_test->login('test_abcde', 'testing');

		// 11
		$users_test->list(403);

		// 12
		// TODO: Fix this on the server.side. The expected status code should be '405' (Method Not Allowed), not 200 (OK).
		$sessions_test->set_new('test_abcde', 'testing2', 200, '"Error: Locked account!"');

		// 13
		// TODO: Fix this on the server.side. The expected status code should be '405' (Method Not Allowed), not 200 (OK).
		$sessions_test->set_old('test_abcde', 'testing2', 'test_abcde', 'testing2', 200, '"Error: Locked account!"');

		// 14
		$sessions_test->logout();

		// 15
		AccessControl::log_as_id(0);

		// 16
		$users_test->save($id, 'test_abcde', 'testing', '0', '0');

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
		// TODO: Fix this on the server.side. The expected status code should be '401' (Not Authorized), not 200 (OK).
		$sessions_test->set_new('test_abcde', 'testing2', 200, '"Error: Old username or password entered incorrectly!"');

		// 23
		// TODO: Fix this on the server.side. The expected status code should be '401' (Not Authorized), not 200 (OK).
		$sessions_test->set_old('test_abcde', 'testing2', 'test_abcde', 'testing2', 200, '"Error: Old username or password entered incorrectly!"');

		// 24
		// TODO: Fix this on the server.side. The expected status code should be '409' (Conflict), not 200 (OK).
		$sessions_test->set_old('test_abcd2', 'testing2', 'test_abcde', 'testing2', 200, '"Error: Username already exists."');

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
		$users_test->save('-1', 'toto', 'test', '0', '1', 422);

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
		$users_test->update_email('test@example.com');

		// 35
		$users_test->get_email();

		// 36
		$users_test->update_email('');

		// 37
		$sessions_test->logout();
		Configs::set('new_photos_notification', $store_new_photos_notification);
	}
}
