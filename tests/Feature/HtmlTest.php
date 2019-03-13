<?php

namespace Tests\Feature;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
use Tests\TestCase;

class HtmlTest extends TestCase
{

	/**
	 * Testing the Login interface
	 *
	 * @return void
	 */
	public function testLogin()
	{

		/**
		 * because there is no dependency injection in test cases
		 */
		$sessionFunctions = new SessionFunctions();

		/**
		 * check if we can actually get a nice answer
		 */
		$response = $this->get('/');
		$response->assertOk();

		$clear = false;
		$configs = Configs::get();

		/**
		 * Check if password and username are set
		 */
		if ($configs['password'] == '' && $configs['username'] == '') {
			$clear = true;

			/**
			 * If they are not this means we are in the install step.
			 * We check Settings::setLogin
			 */
			$response = $this->post('/api/Settings::setLogin', [
				'username' => 'lychee',
				'password' => 'password'
			]);
			$response->assertSee("true");

			/**
			 * We log out (when creating we are automatically logged in as Admin)
			 */
			$response = $this->post('/api/Session::logout');
			$response->assertSee("true");

			/**
			 * We try to log in with the username and password
			 */
			$response = $this->post('/api/Session::login', ['user'     => 'lychee', 'password' => 'password' ]);
			$response->assertSee("true");

			/**
			 * We log out (We are going to test wrong username - password)
			 */
			$response = $this->post('/api/Session::logout');
			$response->assertSee("true");

		}

		/**
		 * We check that there are username and password set in the database
		 */
		$this->assertFalse($sessionFunctions->noLogin());


		/**
		 * Try to login with wrong login and wrong password
		 */
		$response = $this->post('/api/Session::login', ['user'     => 'foo',
		                                                'password' => 'bar'
		]);
		$response->assertSee("false");

		/**
		 * Try to login with correct test login and wrong password
		 */
		$response = $this->post('/api/Session::login', [
			'user'     => 'lychee',
			'password' => 'bar'
		]);
		$response->assertSee("false");

		/**
		 * Try to login with wrong login and correct test password
		 */
		$response = $this->post('/api/Session::login', [
			'user'     => 'foo',
			'password' => 'password'
		]);
		$response->assertSee("false");

		/**
		 * If we did set login and password we clear them
		 */
		if ($clear) {
			Configs::set('username', '');
			Configs::set('password', '');
		}
	}
}
