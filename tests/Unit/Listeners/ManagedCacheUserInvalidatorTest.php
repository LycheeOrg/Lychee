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
use App\Listeners\ManagedCacheUserInvalidator;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class ManagedCacheUserInvalidatorTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testHandleEvictsTheUsersTag(): void
	{
		Configs::set('managed_cache_enabled', '1');
		$cache_service = new ManagedCacheService(new ConfigManager());
		$listener = new ManagedCacheUserInvalidator($cache_service);

		$key = 'mcui-test:' . uniqid();
		$cache_service->remember($key, ['user:42'], 60, fn () => 'value');
		self::assertNotNull(Cache::get($key));

		$listener->handle(new UserGroupMembershipChanged(42));

		self::assertNull(Cache::get($key));
	}
}
