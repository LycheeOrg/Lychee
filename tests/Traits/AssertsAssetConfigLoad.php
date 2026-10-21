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

namespace Tests\Traits;

use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

/**
 * Binary routes (`/Asset`, `/Faces/{face_id}/photo/{variant}`) load only the
 * config keys they read, in one query, and run no SE verification
 * (Feature 102, FR-102-01, FR-102-03).
 */
trait AssertsAssetConfigLoad
{
	/**
	 * Runs the request as the first one of a fresh request lifecycle and
	 * asserts its `configs` reads. The test shares one container across
	 * requests (Octane clones it per request), so the scoped `ConfigManager`
	 * binding of a previous request would otherwise answer without a query.
	 *
	 * @param \Closure(): TestResponse $request
	 */
	protected function assertLoadsListedConfigsOnly(\Closure $request): TestResponse
	{
		app()->forgetScopedInstances();
		app()->offsetUnset(ConfigManager::class);
		DB::flushQueryLog();
		DB::enableQueryLog();
		$response = $request();
		DB::disableQueryLog();

		$queries = array_values(array_filter(
			DB::getQueryLog(),
			fn (array $entry) => preg_match('/[`"\']configs[`"\']/', $entry['query']) === 1)
		);
		$dump = implode("\n", array_map(fn (array $entry) => $entry['query'] . ' ' . json_encode($entry['bindings']), $queries));

		self::assertCount(1, $queries, $dump);
		self::assertMatchesRegularExpression('/ in \(/i', $queries[0]['query'], $dump);
		self::assertNotContains('license_key', $queries[0]['bindings'], $dump);
		self::assertNotContains('email', $queries[0]['bindings'], $dump);

		return $response;
	}
}
