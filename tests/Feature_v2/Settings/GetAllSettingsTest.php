<?php

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
	}

	public function testGetAllSettingUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Settings');
		$this->assertForbidden($response);
	}

	public function testGetAllSettingsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);
		// dd($response->json());
		$response->assertJson([
			'configs' => [
				'Admin' => [
					[
						'key' => 'version',
						'documentation' => 'Current version of Lychee',
					],
				],
				'config' => [],
				'Footer' => [],
				'Gallery' => [],
				'Image Processing' => [],
			],
		]);
	}
}