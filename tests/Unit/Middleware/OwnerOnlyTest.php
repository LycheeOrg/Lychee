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

use App\Exceptions\UnauthorizedException;
use App\Http\Middleware\OwnerOnly;
use App\Models\Configs;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\AbstractTestCase;

class OwnerOnlyTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testUnauthenticatedIsRejected(): void
	{
		$owner = User::factory()->may_administrate()->create();
		Configs::set('owner_id', $owner->id);

		$middleware = new OwnerOnly(resolve(ConfigManager::class));
		$request = Request::create('/admin/profiler');

		$this->assertThrows(
			fn () => $middleware->handle($request, fn () => 'ok'),
			UnauthorizedException::class
		);
	}

	public function testNonOwnerIsRejected(): void
	{
		$owner = User::factory()->may_administrate()->create();
		$other = User::factory()->may_administrate()->create();
		Configs::set('owner_id', $owner->id);

		$this->actingAs($other);

		$middleware = new OwnerOnly(resolve(ConfigManager::class));
		$request = Request::create('/admin/profiler');

		$this->assertThrows(
			fn () => $middleware->handle($request, fn () => 'ok'),
			UnauthorizedException::class
		);
	}

	public function testOwnerPassesThrough(): void
	{
		$owner = User::factory()->may_administrate()->create();
		Configs::set('owner_id', $owner->id);

		$this->actingAs($owner);

		$middleware = new OwnerOnly(resolve(ConfigManager::class));
		$request = Request::create('/admin/profiler');

		self::assertSame('ok', $middleware->handle($request, fn () => 'ok'));
	}
}
