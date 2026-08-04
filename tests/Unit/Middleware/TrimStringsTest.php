<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

namespace Tests\Unit\Middleware;

use App\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Tests\AbstractTestCase;

class TrimStringsTest extends AbstractTestCase
{
	public function testTrimsRegularFieldsButNotExcludedOnes(): void
	{
		$request = Request::create('/', 'POST', [
			'title' => '  My Title  ',
			'password' => '  secret  ',
			'password_confirmation' => '  secret  ',
			'needle' => '  find me  ',
			'replacement' => '  replace me  ',
		]);
		$middleware = new TrimStrings();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));

		self::assertEquals('My Title', $request->input('title'));
		self::assertEquals('  secret  ', $request->input('password'));
		self::assertEquals('  secret  ', $request->input('password_confirmation'));
		self::assertEquals('  find me  ', $request->input('needle'));
		self::assertEquals('  replace me  ', $request->input('replacement'));
	}
}
