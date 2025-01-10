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

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;

class DiagnosticsTest extends AbstractTestCase
{
	/**
	 * Test diagnostics.
	 *
	 * @return void
	 */
	public function testDiagnostics(): void
	{
		$response = $this->get('/Diagnostics');
		$this->assertOk($response); // code 200 something

		Auth::loginUsingId(1);

		$response = $this->get('/Diagnostics');
		$this->assertOk($response); // code 200 something

		$response = $this->get('/Permissions');
		$this->assertOk($response); // code 200 something too

		Configs::query()->where('key', '=', 'lossless_optimization')->update(['value' => null]);

		$response = $this->postJson('/api/Diagnostics::get');
		$this->assertOk($response); // code 200 something too

		$response = $this->postJson('/api/Diagnostics::getSize');
		$this->assertOk($response); // code 200 something too

		Configs::query()->where('key', '=', 'lossless_optimization')->update(['value' => '1']);

		Auth::logout();
		Session::flush();
	}
}
