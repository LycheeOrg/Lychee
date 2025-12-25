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

namespace Tests\Feature_v2\Maintenance;

use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class StatisticsIntegrityTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set('metrics_enabled', true);

	}

	public function tearDown(): void
	{
		Configs::set('metrics_enabled', false);

		parent::tearDown();
	}

	public function testGuest(): void
	{
		$response = $this->getJsonWithData('Maintenance::statisticsIntegrity', []);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Maintenance::statisticsIntegrity', []);
		$this->assertUnauthorized($response);
	}

	public function testUser(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Maintenance::statisticsIntegrity', []);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Maintenance::statisticsIntegrity', []);
		$this->assertForbidden($response);
	}

	public function testAdmin(): void
	{
		DB::table('statistics')->truncate();

		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::statisticsIntegrity', []);
		$this->assertOk($response);
		$response->assertJsonPath('missing_albums', 9);
		$response->assertJsonPath('missing_photos', 9);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::statisticsIntegrity', []);
		$this->assertCreated($response);
		$response->assertJsonPath('missing_albums', 0);
		$response->assertJsonPath('missing_photos', 0);
	}

	public function testAdminWithDisabledMetrics(): void
	{
		Configs::set('metrics_enabled', false);

		DB::table('statistics')->truncate();
		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::statisticsIntegrity', []);
		$this->assertOk($response);
		$response->assertJsonPath('missing_albums', 0);
		$response->assertJsonPath('missing_photos', 0);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::statisticsIntegrity', []);
		$this->assertCreated($response);
		$response->assertJsonPath('missing_albums', 0);
		$response->assertJsonPath('missing_photos', 0);
	}
}