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

use App\Models\Logs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;

class LogsTest extends AbstractTestCase
{
	/**
	 * Test log handling.
	 *
	 * @return void
	 */
	public function testLogs(): void
	{
		$response = $this->get('/Logs');
		$this->assertForbidden($response);

		// set user as admin
		Auth::loginUsingId(0);

		Logs::notice(__METHOD__, __LINE__, 'test');
		$response = $this->get('/Logs');
		$this->assertOk($response);
		$response->assertViewIs('logs.list');

		Auth::logout();
		Session::flush();
	}

	public function testApiLogs(): void
	{
		$response = $this->postJson('/api/Logs::list');
		$this->assertForbidden($response);
	}

	public function testClearLogs(): void
	{
		$response = $this->postJson('/api/Logs::clearNoise');
		$this->assertForbidden($response);

		$response = $this->postJson('/api/Logs::clear');
		$this->assertForbidden($response);

		// set user as admin
		Auth::loginUsingId(0);

		$response = $this->postJson('/api/Logs::clearNoise');
		$this->assertNoContent($response);

		$response = $this->postJson('/api/Logs::clear');
		$this->assertNoContent($response);

		$response = $this->get('/Logs');
		$this->assertOk($response);
		$response->assertSeeText('Everything looks fine, Lychee has not reported any problems!');

		Auth::logout();
		Session::flush();
	}
}
