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
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Feature\Traits\CatchFailures;
use Tests\TestCase;

class LogsTest extends TestCase
{
	use CatchFailures;

	private string $saveUsername;
	private string $savedPassword;
	private User $admin;

	/**
	 * Test log handling.
	 *
	 * @return void
	 */
	public function testLogs(): void
	{
		$this->initAdmin();

		$response = $this->get('/Logs');
		$this->assertUnauthorized($response);

		// set user as admin
		Auth::loginUsingId(1);

		Logs::notice(__METHOD__, __LINE__, 'test');
		$response = $this->get('/Logs');
		$this->assertOk($response);
		$response->assertViewIs('logs.list');

		Auth::logout();
		Session::flush();

		$this->revertAdmin();
	}

	public function testApiLogs(): void
	{
		$this->initAdmin();

		$response = $this->postJson('/api/Logs::list');
		$this->assertUnauthorized($response);

		$this->revertAdmin();
	}

	public function testClearLogs(): void
	{
		$response = $this->postJson('/api/Logs::clearNoise');
		$this->assertUnauthorized($response);

		$response = $this->postJson('/api/Logs::clear');
		$this->assertUnauthorized($response);

		// set user as admin
		Auth::loginUsingId(1);

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

	private function initAdmin(): void
	{
		$this->admin = User::find(1);
		$this->name = $this->admin->username;
		$this->pw = $this->admin->password;
		$this->admin->username = 'temp';
		$this->admin->password = 'temp';
		$this->admin->save();
	}

	private function revertAdmin(): void
	{
		$this->admin = User::find(1);
		$this->admin->username = $this->name;
		$this->admin->password = $this->pw;
		$this->admin->save();
	}
}
