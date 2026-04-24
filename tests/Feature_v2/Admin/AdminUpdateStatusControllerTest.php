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

use App\Actions\InstallUpdate\CheckUpdate;
use App\Enum\UpdateStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AdminUpdateStatusControllerTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.update-check', true);
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
		$response->assertExactJson([
			'enabled' => false,
			'update_status' => null,
			'has_update' => false,
			'current_version' => null,
			'latest_version' => null,
		]);
	}

	public function testAdminReceivesUpdateStatusData(): void
	{
		/** @var Authenticatable $admin */
		$admin = $this->admin;

		$this->mock(CheckUpdate::class, function ($mock) {
			$mock->shouldReceive('getCode')->once()->andReturn(UpdateStatus::NOT_UP_TO_DATE);
			$mock->shouldReceive('getCurrentVersion')->once()->andReturn('5.2.0');
			$mock->shouldReceive('getLatestVersion')->once()->andReturn('5.3.1');
		});

		$response = $this->actingAs($admin)->getJson('Admin/UpdateStatus');
		$this->assertOk($response);
		$response->assertExactJson([
			'enabled' => true,
			'update_status' => UpdateStatus::NOT_UP_TO_DATE->value,
			'has_update' => true,
			'current_version' => '5.2.0',
			'latest_version' => '5.3.1',
		]);
	}
}
