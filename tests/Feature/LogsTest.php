<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use AccessControl;
use App\Models\Logs;
use Tests\TestCase;

class LogsTest extends TestCase
{
	/**
	 * Test log handling.
	 *
	 * @return void
	 */
	public function testLogs()
	{
		$response = $this->get('/Logs');
		$response->assertForbidden();

		// set user as admin
		AccessControl::log_as_id(0);

		Logs::notice(__METHOD__, __LINE__, 'test');
		$response = $this->get('/Logs');
		$response->assertOk();
		$response->assertViewIs('logs.list');

		AccessControl::logout();
	}

	public function testApiLogs()
	{
		$response = $this->postJson('/api/Logs');
		$response->assertForbidden();
	}

	public function testClearLogs()
	{
		$response = $this->postJson('/api/Logs::clearNoise');
		$response->assertForbidden();

		$response = $this->postJson('/api/Logs::clear');
		$response->assertForbidden();

		// set user as admin
		AccessControl::log_as_id(0);

		$response = $this->postJson('/api/Logs::clearNoise');
		$response->assertNoContent();

		$response = $this->postJson('/api/Logs::clear');
		$response->assertNoContent();

		$response = $this->get('/Logs');
		$response->assertOk();
		$response->assertSeeText('Everything looks fine, Lychee has not reported any problems!');

		AccessControl::logout();
	}
}
