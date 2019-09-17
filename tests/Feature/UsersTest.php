<?php

namespace Tests\Feature;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
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

		/*
		 * Scenario is as follow
		 *
		 * 1. log as admin
		 * 2. create a user 'test_abcd'
		 * 3. list users
		 * 4. check if the user is found
		 * 5. create a user 'test_abcd' => should return an error: no duplicate
		 * 6. change username and password of user
		 * 7. log out
		 * 8. log as 'test_abcde'
		 * 9. try to list users => should fail
		 * 10. change password => should fail (account is locked)
		 * 11. log out (did not test upload)
		 *
		 * 12. log as admin
		 * 13. unlock account
		 * 14. log out
		 *
		 * 15. log as 'test_abcde'
		 * 16. change password
		 * 17. log out
		 *
		 * 18. log as 'test_abcde'
		 * 19. log out
		 *
		 * 20. log as admin
		 * 21. delete user
		 * 22. log out
		 */

		// 1
		$sessions_test->log_as_id(0);

		// 2
		$users_test->add($this, 'test_abcd', 'test_abcd', '1', '1', 'true');

		// 3
		$response = $users_test->list($this, 'true');

		// 4
		$user_list = json_decode($response->getContent());
		$num = count($user_list);
		$id = $user_list[$num - 1]->id;
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
		$sessions_test->logout($this);

		// 8
		$sessions_test->login($this, 'test_abcde', 'testing');

		// 9
		$users_test->list($this, 'false');

		// 10
		$sessions_test->set_new($this, 'test_abcde', 'testing2', '"Error: Locked account!"');
		$sessions_test->set_old($this, 'test_abcde', 'testing2', 'test_abcde', 'testing2', '"Error: Locked account!"');

		// 11
		$sessions_test->logout($this);

		// 12
		$sessions_test->log_as_id(0);

		// 13
		$users_test->save($this, $id, 'test_abcde', 'testing', '0', '0', 'true');

		// 14
		$sessions_test->logout($this);

		// 15
		$sessions_test->login($this, 'test_abcde', 'testing');

		// 16
		$sessions_test->set_new($this, 'test_abcde', 'testing2', '"Error: Old username or password entered incorrectly!"');
		$sessions_test->set_old($this, 'test_abcde', 'testing2', 'test_abcde', 'testing2', '"Error: Old username or password entered incorrectly!"');
		$sessions_test->set_old($this, 'test_abcdef', 'testing2', 'test_abcde', 'testing', 'true');

		// 17
		$sessions_test->logout($this);

		// 18
		$sessions_test->login($this, 'test_abcdef', 'testing2');

		// 19
		$sessions_test->logout($this);

		// 20
		$sessions_test->log_as_id(0);

		// 21
		$users_test->delete($this, $id, 'true');

		// 22
		$sessions_test->logout($this);
	}
}
