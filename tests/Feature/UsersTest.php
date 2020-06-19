<?php

namespace Tests\Feature;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\TestCase;

class UsersTest extends TestCase
{
	public function test_set_Login()
	{
		/**
		 * because there is no dependency injection in test cases.
		 */
		$sessionFunctions = new SessionFunctions();
		$sessions_test = new SessionUnitTest();

		$clear = false;
		$configs = Configs::get();

		/*
		 * Check if password and username are set
		 */
		if ($configs['password'] == '' && $configs['username'] == '') {
			$clear = true;

			$sessions_test->set_new($this, 'lychee', 'password', 'true');
			$sessions_test->logout($this);

			$sessions_test->login($this, 'lychee', 'password', 'true');
			$sessions_test->logout($this);
		} else {
			$this->markTestSkipped('Username and Password are set. We do not bother testing further.');
		}

		/*
		 * We check that there are username and password set in the database
		 */
		$this->assertFalse($sessionFunctions->noLogin());

		$sessions_test->login($this, 'foo', 'bar', 'false');
		$sessions_test->login($this, 'lychee', 'bar', 'false');
		$sessions_test->login($this, 'foo', 'password', 'false');

		/*
		 * If we did set login and password we clear them
		 */
		if ($clear) {
			Configs::set('username', '');
			Configs::set('password', '');
		}
	}

	public function test_users()
	{
		$sessions_test = new SessionUnitTest();
		$users_test = new UsersUnitTest();
		$album_tests = new AlbumsUnitTest();

		/*
		 * Scenario is as follow
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
		 */

		// 1
		$sessions_test->log_as_id(0);

		// 2
		$users_test->add($this, 'test_abcd', 'test_abcd', '1', '1', 'true');

		// 3
		$response = $users_test->list($this, 'true');

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
		$users_test->add($this, 'test_abcd', 'test_abcd', '1', '1', 'Error: username must be unique');

		// 6
		$users_test->save($this, $id, 'test_abcde', 'testing', '0', '1', 'true');

		// 7
		$users_test->add($this, 'test_abcd2', 'test_abcd', '1', '1', 'true');
		$response = $users_test->list($this, 'true');
		$t = json_decode($response->getContent());
		$id2 = end($t)->id;

		// 8
		$users_test->save($this, $id2, 'test_abcde', 'testing', '0', '1', 'Error: username must be unique');

		// 9
		$sessions_test->logout($this);

		// 10
		$sessions_test->login($this, 'test_abcde', 'testing');

		// 11
		$users_test->list($this, 'false');

		// 12
		$sessions_test->set_new($this, 'test_abcde', 'testing2', '"Error: Locked account!"');

		// 13
		$sessions_test->set_old($this, 'test_abcde', 'testing2', 'test_abcde', 'testing2', '"Error: Locked account!"');

		// 14
		$sessions_test->logout($this);

		// 15
		$sessions_test->log_as_id(0);

		// 16
		$users_test->save($this, $id, 'test_abcde', 'testing', '0', '0', 'true');

		// 17
		$sessions_test->logout($this);

		// 18
		$sessions_test->login($this, 'test_abcde', 'testing');
		$sessions_test->init($this, 'true');

		// 19
		$album_tests->get($this, 's', '', 'true');

		// 20
		$album_tests->get($this, 'f', '', 'true');

		// 21
		$album_tests->get($this, '0', '', 'true');

		// 22
		$sessions_test->set_new($this, 'test_abcde', 'testing2', '"Error: Old username or password entered incorrectly!"');

		// 23
		$sessions_test->set_old($this, 'test_abcde', 'testing2', 'test_abcde', 'testing2', '"Error: Old username or password entered incorrectly!"');

		// 24
		$sessions_test->set_old($this, 'test_abcd2', 'testing2', 'test_abcde', 'testing2', '"Error: Username already exists."');

		// 25
		$sessions_test->set_old($this, 'test_abcdef', 'testing2', 'test_abcde', 'testing', 'true');

		// 26
		$sessions_test->logout($this);

		// 27
		$sessions_test->login($this, 'test_abcdef', 'testing2');

		// 28
		$sessions_test->logout($this);

		// 29
		$sessions_test->log_as_id(0);

		// 30
		$users_test->delete($this, $id, 'true');
		$users_test->delete($this, $id2, 'true');

		// those should fail because there are no user with id -1
		$users_test->delete($this, '-1', 'false');
		$users_test->save($this, '-1', 'toto', 'test', '0', '1', 'false');

		// 31
		$sessions_test->logout($this);
	}
}
