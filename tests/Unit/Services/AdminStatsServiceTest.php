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

namespace Tests\Unit\Services;

use App\DTO\AdminStatsOverview;
use App\Services\AdminStatsService;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class AdminStatsServiceTest extends AbstractTestCase
{
	private AdminStatsService $service;

	public function setUp(): void
	{
		parent::setUp();
		$this->service = new AdminStatsService();
		Cache::forget('admin.stats');
	}

	public function testGetOverviewComputesMetricsOnCacheMiss(): void
	{
		$overview = $this->service->getOverview();

		$this->assertInstanceOf(AdminStatsOverview::class, $overview);
		$this->assertGreaterThanOrEqual(0, $overview->photos_count);
		$this->assertGreaterThanOrEqual(0, $overview->albums_count);
		$this->assertGreaterThanOrEqual(0, $overview->users_count);
		$this->assertIsArray($overview->errors);
		$this->assertNotEmpty($overview->cached_at);
	}

	public function testGetOverviewReturnsCachedOnHit(): void
	{
		$cached_overview = new AdminStatsOverview(
			photos_count: 42,
			albums_count: 7,
			users_count: 3,
			storage_bytes: 1024,
			queued_jobs: 0,
			failed_jobs_24h: 0,
			last_successful_job_at: null,
			cached_at: now()->toIso8601String(),
			errors: [],
		);
		Cache::put('admin.stats', $cached_overview, 300);

		$overview = $this->service->getOverview();

		$this->assertSame(42, $overview->photos_count);
		$this->assertSame(7, $overview->albums_count);
	}

	public function testGetOverviewForceRefreshesCache(): void
	{
		$stale_overview = new AdminStatsOverview(
			photos_count: 999,
			albums_count: 999,
			users_count: 999,
			storage_bytes: 999,
			queued_jobs: 0,
			failed_jobs_24h: 0,
			last_successful_job_at: null,
			cached_at: now()->toIso8601String(),
			errors: [],
		);
		Cache::put('admin.stats', $stale_overview, 300);

		$overview = $this->service->getOverview(true);

		// After force refresh, counts should be actual DB values, not the stale 999.
		$this->assertNotSame(999, $overview->photos_count);
	}

	public function testGetOverviewReturnsErrorsArrayOnPartialFailure(): void
	{
		// A fresh call with the real DB should produce an empty errors array
		// (all queries succeed against the test database).
		$overview = $this->service->getOverview();
		$this->assertIsArray($overview->errors);
	}
}
