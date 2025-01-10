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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;

class OptimizeTest extends AbstractTestCase
{
	public function testDoNotLogged(): void
	{
		$response = $this->get('/Optimize', []);
		$this->assertUnauthorized($response);
	}

	public function testDoLogged(): void
	{
		Auth::loginUsingId(1);
		$response = $this->get('/Optimize', []);
		$this->assertStatus($response, 200);
		$response->assertViewIs('list');
		Auth::logout();
		Session::flush();
	}
}
