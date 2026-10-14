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

namespace Tests\Feature_v3\Frame;

use App\Http\Controllers\Admin\SettingsController;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 088: validation of the Frame overlay settings (FR-088-03,
 * FR-088-04, FR-088-08, FR-088-13, S-088-06).
 */
class FrameOverlaySettingsTest extends BaseApiWithDataTest
{
	private const KEYS = [
		'mod_frame_clock_position',
		'mod_frame_clock_time_format',
		'mod_frame_clock_opacity',
		'mod_frame_clock_background_color',
		'mod_frame_info_position',
		'mod_frame_info_background_color',
		'mod_frame_info_background_opacity',
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
	 * @return array<string,array{0:string,1:string}>
	 */
	public static function invalidValuesProvider(): array
	{
		return [
			'info box in the center' => ['mod_frame_info_position', 'center'],
			'unknown clock position' => ['mod_frame_clock_position', 'middle'],
			'unknown time format' => ['mod_frame_clock_time_format', '36h'],
			'opacity above 100' => ['mod_frame_clock_opacity', '150'],
			'background opacity below 0' => ['mod_frame_info_background_opacity', '-1'],
			'colour name' => ['mod_frame_info_background_color', 'red'],
			'colour without #' => ['mod_frame_clock_background_color', '1e293b'],
		];
	}

	#[DataProvider('invalidValuesProvider')]
	public function testInvalidValueIsRejected(string $key, string $value): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => $key, 'value' => $value]],
		]);
		$this->assertUnprocessable($response);
		$response->assertSee('is not a valid configuration value');
	}

	/**
	 * @return array<string,array{0:string,1:string}>
	 */
	public static function validValuesProvider(): array
	{
		return [
			'clock in the center' => ['mod_frame_clock_position', 'center'],
			'info box top right' => ['mod_frame_info_position', 'top_right'],
			'12-hour time' => ['mod_frame_clock_time_format', '12h'],
			'opacity 0' => ['mod_frame_clock_opacity', '0'],
			'background opacity 100' => ['mod_frame_info_background_opacity', '100'],
			'hex colour' => ['mod_frame_info_background_color', '#1e293b'],
			'empty colour' => ['mod_frame_clock_background_color', ''],
		];
	}

	#[DataProvider('validValuesProvider')]
	public function testValidValueIsSaved(string $key, string $value): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => $key, 'value' => $value]],
		]);
		$this->assertOk($response);
		self::assertSame($value, strval(Configs::query()->where('key', '=', $key)->value('value')));
	}

	/**
	 * @return string[]
	 */
	private function overlayKeys(): array
	{
		return Configs::query()
			->where('key', 'LIKE', 'mod_frame_clock_%')
			->orWhere('key', 'LIKE', 'mod_frame_info_%')
			->pluck('key')
			->all();
	}

	/**
	 * @return string[]
	 */
	private function settingsKeys(): array
	{
		$response = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($response);

		return collect($response->json())->flatMap(fn ($category) => array_column($category['configs'], 'key'))->all();
	}

	public function testOverlaySettingsHiddenWithoutV8(): void
	{
		Config::set('features.v8', false);
		$keys = $this->settingsKeys();
		foreach ($this->overlayKeys() as $key) {
			self::assertNotContains($key, $keys);
		}
		// Sub-album selection is server-side and applies to the v7 Frame too.
		self::assertContains('mod_frame_include_subalbums', $keys);
	}

	public function testOverlaySettingsVisibleWithV8(): void
	{
		Config::set('features.v8', true);
		$keys = $this->settingsKeys();
		foreach ($this->overlayKeys() as $key) {
			self::assertContains($key, $keys);
		}
	}

	public function testOverlayKeysAreAllV8Only(): void
	{
		$keys = $this->overlayKeys();
		self::assertNotEmpty($keys);
		self::assertEqualsCanonicalizing($keys, array_values(array_intersect(SettingsController::V8_CONFIGS, $keys)));
	}

	public function testFrameSettingsRequireModFrame(): void
	{
		$keys = Configs::query()->where('key', 'LIKE', 'mod_frame_%')->where('key', '!=', 'mod_frame_enabled')->pluck('key')->all();
		$keys[] = 'hide_nsfw_in_frame';
		foreach ($keys as $key) {
			self::assertSame('mod_frame_enabled', Configs::query()->where('key', '=', $key)->value('required_keys'), $key);
		}
	}
}
