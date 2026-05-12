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

namespace Tests\Feature_v2\Admin;

use App\DTO\AdminStatsOverview;
use App\Services\AdminStatsService;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AdminStatsControllerTest extends BaseApiWithDataTest
{
	public function testUnauthenticatedReturns401(): void
	{
		$response = $this->getJson('Admin/Stats');
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReturns403(): void
	{
		$response = $this->actingAs($this->userLocked)->getJson('Admin/Stats');
		$this->assertForbidden($response);
	}

	public function testAdminReturns200WithCorrectStructure(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Admin/Stats');
		$this->assertOk($response);
		$response->assertJsonStructure([
			'photos_count',
			'albums_count',
			'users_count',
			'storage_bytes',
			'queued_jobs',
			'failed_jobs_24h',
			'last_successful_job_at',
			'cached_at',
			'errors',
		]);
	}

	public function testAdminWithForceParamReturns200(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Admin/Stats?force=1');
		$this->assertOk($response);
	}

	public function testPartialErrorReturnsNonEmptyErrors(): void
	{
		$overview = new AdminStatsOverview(
			photos_count: 0,
			albums_count: 0,
			users_count: 0,
			storage_bytes: 0,
			queued_jobs: 0,
			failed_jobs_24h: 0,
			last_successful_job_at: null,
			cached_at: now()->toIso8601String(),
			errors: ['Failed to count photos: some error'],
		);

		$this->mock(AdminStatsService::class, function ($mock) use ($overview) {
			$mock->shouldReceive('getOverview')->once()->andReturn($overview);
		});

		$response = $this->actingAs($this->admin)->getJson('Admin/Stats');
		$this->assertOk($response);
		$response->assertJsonPath('errors.0', 'Failed to count photos: some error');
	}
}
