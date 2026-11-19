<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Admin;

use App\Actions\InstallUpdate\CheckUpdateAvailability;
use App\DTO\UpdateAvailability;
use App\Models\Configs;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AdminUpdateStatusControllerTest extends BaseApiWithDataTest
{
	private const DISABLED_PAYLOAD = [
		'enabled' => false,
		'is_new_release_available' => false,
		'is_git_update_available' => false,
		'commits_behind' => null,
		'current_version' => null,
		'latest_version' => null,
	];

	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.update-check', true);
		Configs::set('check_for_updates', '1');
	}

	public function tearDown(): void
	{
		Configs::set('check_for_updates', '0');
		parent::tearDown();
	}

	public function testUnauthenticatedReturns401(): void
	{
		$response = $this->getJson('Admin/UpdateStatus');
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReturns403(): void
	{
		/** @var Authenticatable $user */
		$user = $this->userLocked;

		$response = $this->actingAs($user)->getJson('Admin/UpdateStatus');
		$this->assertForbidden($response);
	}

	public function testFeatureDisabledReturnsDisabledPayload(): void
	{
		Config::set('features.update-check', false);

		/** @var Authenticatable $admin */
		$admin = $this->admin;

		$response = $this->actingAs($admin)->getJson('Admin/UpdateStatus');
		$this->assertOk($response);
		$response->assertExactJson(self::DISABLED_PAYLOAD);
	}

	public function testConfigDisabledReturnsDisabledPayload(): void
	{
		Configs::set('check_for_updates', '0');

		/** @var Authenticatable $admin */
		$admin = $this->admin;

		$response = $this->actingAs($admin)->getJson('Admin/UpdateStatus');
		$this->assertOk($response);
		$response->assertExactJson(self::DISABLED_PAYLOAD);
	}

	public function testAdminReceivesReleaseUpdate(): void
	{
		$this->mockAvailability(new UpdateAvailability(
			enabled: true,
			is_new_release_available: true,
			is_git_update_available: false,
			commits_behind: null,
			current_version: '5.2.0',
			latest_version: '5.3.1',
		));

		/** @var Authenticatable $admin */
		$admin = $this->admin;

		$response = $this->actingAs($admin)->getJson('Admin/UpdateStatus');
		$this->assertOk($response);
		$response->assertExactJson([
			'enabled' => true,
			'is_new_release_available' => true,
			'is_git_update_available' => false,
			'commits_behind' => null,
			'current_version' => '5.2.0',
			'latest_version' => '5.3.1',
		]);
	}

	public function testAdminReceivesGitUpdate(): void
	{
		$this->mockAvailability(new UpdateAvailability(
			enabled: true,
			is_new_release_available: false,
			is_git_update_available: true,
			commits_behind: 3,
			current_version: '7.10.0',
			latest_version: '7.10.0',
		));

		/** @var Authenticatable $admin */
		$admin = $this->admin;

		$response = $this->actingAs($admin)->getJson('Admin/UpdateStatus');
		$this->assertOk($response);
		$response->assertExactJson([
			'enabled' => true,
			'is_new_release_available' => false,
			'is_git_update_available' => true,
			'commits_behind' => 3,
			'current_version' => '7.10.0',
			'latest_version' => '7.10.0',
		]);
	}

	private function mockAvailability(UpdateAvailability $availability): void
	{
		$this->mock(CheckUpdateAvailability::class, function ($mock) use ($availability) {
			$mock->shouldReceive('get')->once()->andReturn($availability);
		});
	}
}
