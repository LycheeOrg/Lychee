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

namespace Tests\Feature_v2\Caching;

use App\Metadata\Cache\RouteCacheManager;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Tests\Feature_v2\Base\BaseApiV2Test;

class CachingConfigTest extends BaseApiV2Test
{
	private ConsoleSectionOutput $msgSection;
	private bool $failed = false;

	public function testCacheRouteList(): void
	{
		$this->msgSection = (new ConsoleOutput())->section();

		$routeCollection = Route::getRoutes();
		$getRoutes = array_filter($routeCollection->getRoutesByMethod()['GET'], fn ($r) => str_starts_with($r->uri(), ltrim(self::API_PREFIX, '/')));
		$getRoutesUri = array_map(fn ($r) => $r->uri(), $getRoutes);

		$routeManage = new RouteCacheManager();
		$routeCachedConfigured = array_keys($routeManage->cache_list);
		$missingKeys = array_diff($getRoutesUri, $routeCachedConfigured);

		foreach ($missingKeys as $key) {
			$this->msgSection->writeln(sprintf('<comment>Error:</comment>  %s is missing in RouteCacheManager.php', $key));
			$this->failed = true;
		}
		static::assertFalse($this->failed);
	}
}
