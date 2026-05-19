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

namespace Tests\Feature_v2\Settings;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class GetAllSettingsTest extends BaseApiWithDataTest
{
	public function testGetAllSettingsGuest(): void
	{
		$response = $this->getJson('Settings');
		$this->assertUnauthorized($response);

		$response = $this->getJson('Settings::init');
		$this->assertUnauthorized($response);

		$response = $this->getJson('Settings::getLanguages');
		$this->assertUnauthorized($response);
	}

	public function testGetAllSettingUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Settings');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Settings::init');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Settings::getLanguages');
		$this->assertForbidden($response);
	}

	public function testGetAllSettingsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);
		$response->assertJson([
			[
				'cat' => 'config',
				'name' => 'Basics',
			],
			[
				'cat' => 'lychee SE',
				'name' => 'Lychee SE',
			],
			[
				'cat' => 'Gallery',
				'name' => 'Gallery',
			],
			[
				'cat' => 'Mod Welcome',
				'name' => 'Landing page',
			],
		]);

		// Mod Cache must be hidden by default (ENABLE_REQUEST_CACHING defaults to false).
		$response->assertJsonMissing(['cat' => 'Mod Cache']);

		$response = $this->actingAs($this->admin)->getJson('Settings::init');
		$this->assertOk($response);
		$response->assertJson([
			'default_old_settings' => '0',
			'default_expert_settings' => '0',
			'default_all_settings' => '0',
		]);

		$response = $this->actingAs($this->admin)->getJson('Settings::getLanguages');
		$this->assertOk($response);
	}

	public function testModCacheVisibleWhenFeatureEnabled(): void
	{
		config(['features.enable-request-caching' => true]);

		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);
		$response->assertJsonFragment(['cat' => 'Mod Cache']);
	}

	public function testModCacheHiddenWhenFeatureDisabled(): void
	{
		config(['features.enable-request-caching' => false]);

		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);
		$response->assertJsonMissing(['cat' => 'Mod Cache']);
	}
}