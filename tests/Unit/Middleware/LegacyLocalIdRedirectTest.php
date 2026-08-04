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

use App\Http\Middleware\LegacyLocalIdRedirect;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\AbstractTestCase;

class LegacyLocalIdRedirectTest extends AbstractTestCase
{
	public function testPassesThroughWhenAlbumIdIsNull(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn(null);
		});
		$middleware = new LegacyLocalIdRedirect();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testPassesThroughWhenAlbumIdDoesNotMatchLegacyFormat(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('not-a-legacy-id');
		});
		$middleware = new LegacyLocalIdRedirect();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testPassesThroughWhenLegacyColumnMissing(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('12345678901234');
		});
		Schema::shouldReceive('hasColumn')->once()->with('base_albums', 'legacy_id')->andReturn(false);
		$middleware = new LegacyLocalIdRedirect();

		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testThrowsNotFoundWhenLegacyIdUnknown(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('12345678901234');
		});
		Schema::shouldReceive('hasColumn')->once()->with('base_albums', 'legacy_id')->andReturn(true);

		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('where')->once()->with('legacy_id', '=', '12345678901234')->andReturnSelf();
		$builder->shouldReceive('first')->once()->andReturn(null);
		DB::shouldReceive('table')->once()->with('base_albums')->andReturn($builder);

		$middleware = new LegacyLocalIdRedirect();

		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1), NotFoundHttpException::class);
	}

	public function testRedirectsWhenLegacyIdResolved(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('12345678901234');
		});
		Schema::shouldReceive('hasColumn')->once()->with('base_albums', 'legacy_id')->andReturn(true);

		$builder = \Mockery::mock(Builder::class);
		$builder->shouldReceive('where')->once()->with('legacy_id', '=', '12345678901234')->andReturnSelf();
		$builder->shouldReceive('first')->once()->andReturn((object) ['id' => 'abc123456789012345678901']);
		DB::shouldReceive('table')->once()->with('base_albums')->andReturn($builder);

		$middleware = new LegacyLocalIdRedirect();
		$response = $middleware->handle($request, fn () => 1);

		self::assertEquals(301, $response->getStatusCode());
		self::assertStringContainsString('abc123456789012345678901', $response->headers->get('Location'));
	}
}
