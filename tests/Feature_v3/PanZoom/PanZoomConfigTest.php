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

namespace Tests\Feature_v3\PanZoom;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 078 – lightbox pan & zoom configs exposed through Gallery::Init (S-078-01).
 */
class PanZoomConfigTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'photo_click_action',
		'is_photo_minimap_enabled',
		'is_photo_minimap_enabled_mobile',
		'photo_minimap_idle_opacity',
		'photo_minimap_idle_opacity_mobile',
		'photo_minimap_fade_delay',
	];

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	/**
	 * @param array<string,string> $values
	 */
	private function setConfigs(array $values): TestResponse
	{
		$configs = [];
		foreach ($values as $key => $value) {
			$configs[] = ['key' => $key, 'value' => $value];
		}

		return $this->actingAs($this->admin)->postJson('Settings::setConfigs', ['configs' => $configs]);
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

	public function testSettingsHiddenWithoutV8(): void
	{
		Config::set('features.v8', false);
		$keys = $this->settingsKeys();
		foreach (self::KEYS as $key) {
			self::assertNotContains($key, $keys);
		}
	}

	public function testSettingsVisibleWithV8(): void
	{
		Config::set('features.v8', true);
		$keys = $this->settingsKeys();
		foreach (self::KEYS as $key) {
			self::assertContains($key, $keys);
		}
	}

	public function testDefaults(): void
	{
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'photo_click_action' => 'overlay',
			'is_photo_minimap_enabled' => true,
			'is_photo_minimap_enabled_mobile' => true,
			'photo_minimap_idle_opacity' => 25,
			'photo_minimap_idle_opacity_mobile' => 25,
			'photo_minimap_fade_delay' => 2,
		]);
	}

	public function testUpdatedValues(): void
	{
		$this->assertOk($this->setConfigs([
			'photo_click_action' => 'zoom',
			'is_photo_minimap_enabled' => '0',
			'is_photo_minimap_enabled_mobile' => '0',
			'photo_minimap_idle_opacity' => '40',
			'photo_minimap_idle_opacity_mobile' => '0',
			'photo_minimap_fade_delay' => '5',
		]));
		resolve(ConfigManager::class)->invalidateCache();

		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'photo_click_action' => 'zoom',
			'is_photo_minimap_enabled' => false,
			'is_photo_minimap_enabled_mobile' => false,
			'photo_minimap_idle_opacity' => 40,
			'photo_minimap_idle_opacity_mobile' => 0,
			'photo_minimap_fade_delay' => 5,
		]);
	}

	public function testInvalidClickActionIsRejected(): void
	{
		$this->assertUnprocessable($this->setConfigs(['photo_click_action' => 'rotate']));
	}

	public function testOpacityOutOfRangeIsRejected(): void
	{
		$this->assertUnprocessable($this->setConfigs(['photo_minimap_idle_opacity' => '101']));
	}

	public function testZeroFadeDelayIsRejected(): void
	{
		$this->assertUnprocessable($this->setConfigs(['photo_minimap_fade_delay' => '0']));
	}
}
