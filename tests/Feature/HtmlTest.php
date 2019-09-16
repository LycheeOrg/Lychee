<?php

namespace Tests\Feature;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class HtmlTest extends TestCase
{
	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function test_home()
	{
		/**
		 * check if we can actually get a nice answer.
		 */
		$response = $this->get('/');
		$response->assertOk();

		$response = $this->post('/php/index.php', []);
		$response->assertOk();

		$response = $this->post('/api/Albums::get', []);
		$response->assertOk();
	}

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

			$sessions_test->set($this, 'lychee', 'password', 'true');
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
}
