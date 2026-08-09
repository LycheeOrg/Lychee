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

namespace Tests\Unit\Listeners;

use App\Events\UserGroupMembershipChanged;
use App\Listeners\ManagedCacheUserListingInvalidator;
use App\Repositories\ConfigManager;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class ManagedCacheUserListingInvalidatorTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	public function testHandleEvictsTheUsersOwnTagOnly(): void
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		$config_manager->shouldReceive('getValueAsBool')->with('managed_cache_enabled')->andReturn(true);
		$cache_service = new ManagedCacheService($config_manager);
		$listener = new ManagedCacheUserListingInvalidator($cache_service);

		$cache_service->remember('k:user1', ['user:1'], 60, fn () => 'value');
		$cache_service->remember('k:user2', ['user:2'], 60, fn () => 'value');

		$listener->handle(new UserGroupMembershipChanged(1));

		self::assertNull(Cache::get('k:user1'));
		self::assertNotNull(Cache::get('k:user2'));
	}
}
