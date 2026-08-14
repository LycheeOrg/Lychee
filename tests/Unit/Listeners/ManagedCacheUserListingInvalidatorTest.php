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
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class ManagedCacheUserListingInvalidatorTest extends AbstractTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		config(['features.enable-caching' => true]);
	}

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
		$cache_key_provider = new CacheKeyProvider();
		$listener = new ManagedCacheUserListingInvalidator($cache_service, $cache_key_provider);

		$cache_service->remember('k:user1', [$cache_key_provider->userTag(1)], fn () => 'value', ttl: 60);
		$cache_service->remember('k:user2', [$cache_key_provider->userTag(2)], fn () => 'value', ttl: 60);

		$listener->handle(new UserGroupMembershipChanged(1));

		self::assertNull(Cache::get('k:user1'));
		self::assertNotNull(Cache::get('k:user2'));
	}
}
