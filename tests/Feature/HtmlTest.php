<?php

namespace Tests\Feature;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
use Tests\TestCase;

class HtmlTest extends TestCase
{

	/**
	 * A basic test example.
	 *
	 * @return void
	 */
	public function testHtml()
	{

		// because there is no dependency injection in test cases
		$sessionFunctions = new SessionFunctions();

		// check if we can actually get a nice answer
		$response = $this->get('/');
		$response->assertOk();

		$clear = false;
		$configs = Configs::get();

		if ($configs['password'] == '' && $configs['username'] == '') {
			$clear = true;
			$response = $this->post('/api/Settings::setLogin', [
				'username' => 'lychee',
				'password' => 'password'
			]);
			$response->assertSee("true");

			$response = $this->post('/api/Session::logout');
			$response->assertSee("true");
		}

		$this->assertFalse($sessionFunctions->noLogin());


		$response = $this->post('/api/Session::login', ['user'     => 'foo',
		                                                'password' => 'bar'
		]);
		$response->assertSee("false");

		$response = $this->post('/api/Session::login', [
			'user'     => 'lychee',
			'password' => 'bar'
		]);
		$response->assertSee("false");

		$response = $this->post('/api/Session::login', [
			'user'     => 'foo',
			'password' => 'password'
		]);
		$response->assertSee("false");

		if ($clear) {
			$response = $this->post('/api/Session::login', ['user'     => 'lychee',
			                                                'password' => 'password'
			]);
			$response->assertSee("true");

			Configs::set('username', '');
			Configs::set('password', '');
		}
	}
}
