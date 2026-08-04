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

use App\Actions\Album\Unlock;
use App\Factories\AlbumFactory;
use App\Http\Middleware\UnlockWithPassword;
use App\Models\Extensions\BaseAlbum;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

class UnlockWithPasswordTest extends AbstractTestCase
{
	public function testPassesThroughWhenAlbumIdIsNull(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn(null);
		});
		$factory = \Mockery::mock(AlbumFactory::class);
		$unlock = \Mockery::mock(Unlock::class);

		$middleware = new UnlockWithPassword($factory, $unlock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	/**
	 * @return array<string,array{0:string}>
	 */
	public static function passthroughAlbumIdsProvider(): array
	{
		return [
			'all' => ['all'],
			'favourites' => ['favourites'],
			'smart album' => ['unsorted'],
		];
	}

	#[DataProvider('passthroughAlbumIdsProvider')]
	public function testPassesThroughForSpecialAlbumIds(string $album_id): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock) use ($album_id): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn($album_id);
		});
		$factory = \Mockery::mock(AlbumFactory::class);
		$unlock = \Mockery::mock(Unlock::class);

		$middleware = new UnlockWithPassword($factory, $unlock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testPassesThroughWhenNoPasswordProvided(): void
	{
		$request = $this->mock(Request::class, function (MockInterface $mock): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('abc123456789012345678901');
			$mock->shouldReceive('filled')->once()->with('password')->andReturn(false);
		});
		$factory = \Mockery::mock(AlbumFactory::class);
		$unlock = \Mockery::mock(Unlock::class);

		$middleware = new UnlockWithPassword($factory, $unlock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testPassesThroughWhenUrlParamUnlockDisabled(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('unlock_password_photos_with_url_param')->andReturn(false);

		$request = $this->mock(Request::class, function (MockInterface $mock) use ($config_manager): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('abc123456789012345678901');
			$mock->shouldReceive('filled')->once()->with('password')->andReturn(true);
			$mock->shouldReceive('configs')->once()->andReturn($config_manager);
		});
		$factory = \Mockery::mock(AlbumFactory::class);
		$unlock = \Mockery::mock(Unlock::class);

		Log::shouldReceive('warning')->once()->with(\Mockery::type('string'));

		$middleware = new UnlockWithPassword($factory, $unlock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testAttemptsUnlockOnSuccess(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('unlock_password_photos_with_url_param')->andReturn(true);

		$album = \Mockery::mock(BaseAlbum::class);

		$request = $this->mock(Request::class, function (MockInterface $mock) use ($config_manager): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('abc123456789012345678901');
			$mock->shouldReceive('filled')->once()->with('password')->andReturn(true);
			$mock->shouldReceive('configs')->once()->andReturn($config_manager);
			$mock->shouldReceive('offsetGet')->once()->with('password')->andReturn('my-password');
		});

		$factory = \Mockery::mock(AlbumFactory::class);
		$factory->shouldReceive('findBaseAlbumOrFail')->once()->with('abc123456789012345678901')->andReturn($album);

		$unlock = \Mockery::mock(Unlock::class);
		$unlock->shouldReceive('do')->once()->with($album, 'my-password');

		$middleware = new UnlockWithPassword($factory, $unlock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}

	public function testFailsSilentlyWhenUnlockThrows(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->once()->with('unlock_password_photos_with_url_param')->andReturn(true);

		$request = $this->mock(Request::class, function (MockInterface $mock) use ($config_manager): void {
			$mock->shouldReceive('route')->once()->with('albumId')->andReturn('abc123456789012345678901');
			$mock->shouldReceive('filled')->once()->with('password')->andReturn(true);
			$mock->shouldReceive('configs')->once()->andReturn($config_manager);
		});

		$factory = \Mockery::mock(AlbumFactory::class);
		$factory->shouldReceive('findBaseAlbumOrFail')->once()->with('abc123456789012345678901')->andThrow(new \Exception('not found'));

		$unlock = \Mockery::mock(Unlock::class);

		$middleware = new UnlockWithPassword($factory, $unlock);
		self::assertEquals(1, $middleware->handle($request, fn () => 1));
	}
}
