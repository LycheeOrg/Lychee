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

namespace Tests\Unit\Jobs;

use App\Jobs\RotateLicenseKeyJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Contract\RotationResult;
use LycheeVerify\Contract\Status;
use LycheeVerify\Rotation;
use LycheeVerify\Verify;
use Tests\AbstractTestCase;

class RotateLicenseKeyJobTest extends AbstractTestCase
{
	public function testSkipsWhenConfigsTableMissing(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andReturn(false);

		$verify = \Mockery::mock(Verify::class);
		$rotation = \Mockery::mock(Rotation::class);

		(new RotateLicenseKeyJob())->handle($verify, $rotation);
		self::assertTrue(true);
	}

	public function testSkipsWhenNotFreeEdition(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andReturn(true);

		$verify = \Mockery::mock(Verify::class);
		$verify->shouldReceive('get_status')->once()->andReturn(Status::SUPPORTER_EDITION);
		$rotation = \Mockery::mock(Rotation::class);

		(new RotateLicenseKeyJob())->handle($verify, $rotation);
		self::assertTrue(true);
	}

	public function testSkipsWhenNoApiKeyConfigured(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andReturn(true);
		Config::set('verify.keygen_api_key', '');

		$verify = \Mockery::mock(Verify::class);
		$verify->shouldReceive('get_status')->once()->andReturn(Status::FREE_EDITION);
		$rotation = \Mockery::mock(Rotation::class);

		(new RotateLicenseKeyJob())->handle($verify, $rotation);
		self::assertTrue(true);
	}

	public function testResetsStatusWhenRotationSucceeds(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andReturn(true);
		Config::set('verify.keygen_api_key', 'some-api-key');

		$verify = \Mockery::mock(Verify::class);
		$verify->shouldReceive('get_status')->once()->andReturn(Status::FREE_EDITION);
		$verify->shouldReceive('reset_status')->once();

		Cache::shouldReceive('forget')->once()->with(Rotation::CACHE_KEY);

		$rotation = \Mockery::mock(Rotation::class);
		$rotation->shouldReceive('rotate')->once()->andReturn(RotationResult::ok());

		(new RotateLicenseKeyJob())->handle($verify, $rotation);
		self::assertTrue(true);
	}

	public function testDoesNotResetStatusWhenRotationFails(): void
	{
		Schema::shouldReceive('hasTable')->once()->with('configs')->andReturn(true);
		Config::set('verify.keygen_api_key', 'some-api-key');

		$verify = \Mockery::mock(Verify::class);
		$verify->shouldReceive('get_status')->once()->andReturn(Status::FREE_EDITION);
		$verify->shouldNotReceive('reset_status');

		Cache::shouldReceive('forget')->once()->with(Rotation::CACHE_KEY);

		$rotation = \Mockery::mock(Rotation::class);
		$rotation->shouldReceive('rotate')->once()->andReturn(RotationResult::fail('nope'));

		(new RotateLicenseKeyJob())->handle($verify, $rotation);
		self::assertTrue(true);
	}
}
