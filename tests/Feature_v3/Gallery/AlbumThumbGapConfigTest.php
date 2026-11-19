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

namespace Tests\Feature_v3\Gallery;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 110 – gap between v8 album tiles exposed through Gallery::Init (FR-110-16).
 */
class AlbumThumbGapConfigTest extends BaseApiWithDataTest
{
	private const KEY = 'album_thumb_gap';

	private string $initial_value = '';

	public function setUp(): void
	{
		parent::setUp();
		$this->initial_value = strval(Configs::query()->where('key', '=', self::KEY)->value('value'));
	}

	public function tearDown(): void
	{
		Configs::query()->where('key', '=', self::KEY)->update(['value' => $this->initial_value]);
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	private function setValue(string $value): TestResponse
	{
		return $this->actingAs($this->admin)->postJson('Settings::setConfigs', ['configs' => [['key' => self::KEY, 'value' => $value]]]);
	}

	/**
	 * @return string[]
	 */
	private function settingsKeys(): array
	{
		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);
		$keys = [];
		foreach ($response->json() as $category) {
			foreach ($category['configs'] as $config) {
				$keys[] = $config['key'];
			}
		}

		return $keys;
	}

	private function assertInitValue(int $expected): void
	{
		resolve(ConfigManager::class)->invalidateCache();
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([self::KEY => $expected]);
	}

	public function testDefault(): void
	{
		$this->assertInitValue(16);
	}

	public function testUpdatedValue(): void
	{
		$this->assertOk($this->setValue('30'));
		$this->assertInitValue(30);
	}

	public function testZeroIsAccepted(): void
	{
		$this->assertOk($this->setValue('0'));
		$this->assertInitValue(0);
	}

	public function testNegativeIsRejected(): void
	{
		$this->assertUnprocessable($this->setValue('-1'));
	}

	public function testNonNumericIsRejected(): void
	{
		$this->assertUnprocessable($this->setValue('abc'));
	}

	public function testSettingHiddenWithoutV8(): void
	{
		Config::set('features.v8', false);
		self::assertNotContains(self::KEY, $this->settingsKeys());
	}

	public function testSettingVisibleWithV8(): void
	{
		Config::set('features.v8', true);
		self::assertContains(self::KEY, $this->settingsKeys());
	}
}
