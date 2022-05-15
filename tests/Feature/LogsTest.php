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
use App\Models\Logs;
use Tests\TestCase;

class LogsTest extends TestCase
{
	/**
	 * Test log handling.
	 *
	 * @return void
	 */
	public function testLogs(): void
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

	public function testApiLogs(): void
	{
		$response = $this->postJson('/api/Logs::list');
		$response->assertForbidden();
	}

	public function testClearLogs(): void
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
