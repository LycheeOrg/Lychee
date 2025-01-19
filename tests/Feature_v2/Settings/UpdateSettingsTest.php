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

class UpdateSettingsTest extends BaseApiV2Test
{
	public function testUpdateSettingsGuest(): void
	{
		$response = $this->postJson('Settings::setConfigs', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'key',
					'value' => 'value',
				],
			],
		]);
		$this->assertUnprocessable($response);
		$response->assertSee('is not a valid configuration key');
		$response->assertDontSee('is not a valid configuration value');

		$response = $this->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => 'value',
				],
			],
		]);
		$this->assertUnprocessable($response);
		$response->assertDontSee('is not a valid configuration key');
		$response->assertSee('is not a valid configuration value');

		$response = $this->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertUnauthorized($response);
	}

	public function testUpdateSettingUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateSettingsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertCreated($response);
	}

	public function testUpdateCssForbidden(): void
	{
		$response = $this->postJson('Settings::setCSS', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Settings::setCSS', [
			'css' => 'body { background-color: red; }',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setCSS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setCSS', [
			'css' => 'body { background-color: red; }',
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateCssAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setCSS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->postJson('Settings::setCSS', [
			'css' => 'body { background-color: red; }',
		]);
		$this->assertNoContent($response);
	}

	public function testupdateJsForbiddne(): void
	{
		$response = $this->postJson('Settings::setJS', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Settings::setJS', [
			'js' => 'console.log("Hello World!");',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setJS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setJS', [
			'js' => 'console.log("Hello World!");',
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateJsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setJS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->postJson('Settings::setJS', [
			'js' => 'console.log("Hello World!");',
		]);
		$this->assertNoContent($response);
	}
}