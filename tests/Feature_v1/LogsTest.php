<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v1;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;

class LogsTest extends AbstractTestCase
{
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

		Log::notice(__METHOD__ . ':' . __LINE__ . 'test');
		$response = $this->get('/Logs');
		$this->assertOk($response);

		Auth::logout();
		Session::flush();

		$this->revertAdmin();
	}

	private function initAdmin(): void
	{
		$this->admin = User::find(1);
		$this->saveUsername = $this->admin->username;
		$this->savedPassword = $this->admin->password;
		$this->admin->username = 'temp';
		$this->admin->password = 'temp';
		$this->admin->save();
	}

	private function revertAdmin(): void
	{
		$this->admin = User::find(1);
		$this->admin->username = $this->saveUsername;
		$this->admin->password = $this->savedPassword;
		$this->admin->save();
	}
}
