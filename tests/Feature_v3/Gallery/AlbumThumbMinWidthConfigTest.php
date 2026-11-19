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
 * Feature 110 – v8 album tile minimum width exposed through Gallery::Init (FR-110-01, FR-110-02).
 */
class AlbumThumbMinWidthConfigTest extends BaseApiWithDataTest
{
	private const KEY = 'album_thumb_min_width';

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

	public function testDefault(): void
	{
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([self::KEY => 175]);
	}

	public function testUpdatedValue(): void
	{
		$this->assertOk($this->setValue('300'));
		resolve(ConfigManager::class)->invalidateCache();

		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([self::KEY => 300]);
	}

	public function testZeroIsRejected(): void
	{
		$this->assertUnprocessable($this->setValue('0'));
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
