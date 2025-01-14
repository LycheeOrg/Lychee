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

namespace Tests\Feature_v2\Settings;

use Tests\Feature_v2\Base\BaseApiV2Test;

class GetAllSettingsTest extends BaseApiV2Test
{
	public function testGetAllSettingsGuest(): void
	{
		$response = $this->getJson('Settings');
		$this->assertUnauthorized($response);

		$response = $this->getJson('Settings::getLanguages');
		$this->assertUnauthorized($response);
	}

	public function testGetAllSettingUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Settings');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Settings::getLanguages');
		$this->assertForbidden($response);
	}

	public function testGetAllSettingsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);
		$response->assertJson([
			'configs' => [
				'Admin' => [],
				'config' => [],
				'Footer' => [],
				'Gallery' => [],
				'Image Processing' => [],
			],
		]);

		$response = $this->actingAs($this->admin)->getJson('Settings::getLanguages');
		$this->assertOk($response);
	}
}