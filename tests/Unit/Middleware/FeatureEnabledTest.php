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

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\FeatureDisabledException;
use App\Http\Middleware\FeatureEnabled;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\AbstractTestCase;

class FeatureEnabledTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testFeatureEnabled(): void
	{
		config(['features.some_feature' => true]);

		$request = $this->mock(Request::class);
		$middleware = new FeatureEnabled();

		self::assertEquals(1, $middleware->handle($request, fn () => 1, 'some_feature'));
	}

	public function testFeatureDisabled(): void
	{
		config(['features.some_feature' => false]);

		$request = $this->mock(Request::class);
		$middleware = new FeatureEnabled();

		$this->assertThrows(
			fn () => $middleware->handle($request, fn () => 1, 'some_feature'),
			FeatureDisabledException::class
		);
	}

	public function testFeatureNotDefined(): void
	{
		$request = $this->mock(Request::class);
		$middleware = new FeatureEnabled();

		$this->assertThrows(
			fn () => $middleware->handle($request, fn () => 1, 'undefined_feature'),
			ConfigurationKeyMissingException::class
		);
	}
}
