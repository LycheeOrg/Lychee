<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use App\Logs;
use App\ModelFunctions\SessionFunctions;
use Tests\TestCase;

class LogsTest extends TestCase
{
	/**
	 * Test log handling.
	 *
	 * @return void
	 */
	public function test_Logs()
	{
		$response = $this->get('/Logs');
		$response->assertOk();
		$response->assertSeeText('false');

		// set user as admin
		$sessionFunctions = new SessionFunctions();
		$sessionFunctions->log_as_id(0);

		Logs::notice(__METHOD__, __LINE__, 'test');
		$response = $this->get('/Logs');
		$response->assertOk();
		$response->assertDontSeeText('false');
		$response->assertViewIs('logs.list');

		$sessionFunctions->logout();
	}

	public function test_api_Logs()
	{
		$response = $this->post('/api/Logs');
		$response->assertStatus(200); // code 200 something

		// we may decide to change for another out there so
	}

	public function test_clear_Logs()
	{
		$response = $this->post('/api/Logs::clearNoise');
		$response->assertOk();
		$response->assertSeeText('false');

		$response = $this->post('/api/Logs::clear');
		$response->assertOk();
		$response->assertSeeText('false');

		// set user as admin
		$sessionFunctions = new SessionFunctions();
		$sessionFunctions->log_as_id(0);

		$response = $this->post('/api/Logs::clearNoise');
		$response->assertOk();
		$response->assertSeeText('Log Noise cleared');

		$response = $this->post('/api/Logs::clear');
		$response->assertOk();
		$response->assertSeeText('Log cleared');

		$response = $this->get('/Logs');
		$response->assertOk();
		$response->assertSeeText('Everything looks fine, Lychee has not reported any problems!');

		$sessionFunctions->logout();
	}
}
