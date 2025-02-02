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

namespace Tests\Unit\Metadata\Cache;

use App\Metadata\Cache\RouteCacheManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class RouteCacheManagerTest extends AbstractTestCase
{
	use DatabaseTransactions;
	private RouteCacheManager $route_cache_manager;

	public function setUp(): void
	{
		parent::setUp();
		$this->route_cache_manager = new RouteCacheManager();
	}

	public function testNoConfig(): void
	{
		Log::shouldReceive('warning')->once();
		self::assertFalse($this->route_cache_manager->get_config('fake_url'));
	}

	public function testConfigFalse(): void
	{
		self::assertFalse($this->route_cache_manager->get_config('api/v2/Version'));
	}

	public function testConfigValid(): void
	{
		self::assertIsObject($this->route_cache_manager->get_config('api/v2/Album'));
	}
}

