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

namespace Tests\Feature_v3\Settings;

use App\Http\Controllers\Admin\SettingsController;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Feature 095 (FR-095-01): date scrubber label formats. Year, month and hour
 * are Supporter Edition settings; the day format is free (Q-095-02).
 * FR-095-08: the multi-day hour header format, also SE.
 */
class ScrubberLabelFormatsSettingsTest extends BaseApiWithDataTest
{
	use RequireSE;

	private const SE_KEYS = ['date_scrubber_format_year', 'date_scrubber_format_month', 'date_scrubber_format_hour', 'timeline_photo_date_format_hour_multi_day'];
	private const FREE_KEY = 'date_scrubber_format_day';

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach ([...self::SE_KEYS, self::FREE_KEY] as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		$this->resetSe();
		parent::tearDown();
	}

	private function setConfig(string $key, string $value): TestResponse
	{
		return $this->actingAs($this->admin)->postJson('Settings::setConfigs', ['configs' => [['key' => $key, 'value' => $value]]]);
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

	public function testDefaultsLevelsAndOrder(): void
	{
		$rows = Configs::query()->whereIn('key', [...self::SE_KEYS, self::FREE_KEY])->get()->keyBy('key');

		self::assertSame('Y', $rows['date_scrubber_format_year']->value);
		self::assertSame('M', $rows['date_scrubber_format_month']->value);
		self::assertSame('d/m', $rows['date_scrubber_format_day']->value);
		self::assertSame('H:00', $rows['date_scrubber_format_hour']->value);

		foreach (self::SE_KEYS as $key) {
			self::assertSame(1, $rows[$key]->level, $key);
			self::assertSame('Mod Timeline', $rows[$key]->cat, $key);
		}
		self::assertSame(0, $rows[self::FREE_KEY]->level);
		self::assertSame('Mod Timeline', $rows[self::FREE_KEY]->cat);

		self::assertSame(23, $rows['date_scrubber_format_year']->order);
		self::assertSame(24, $rows['date_scrubber_format_month']->order);
		self::assertSame(25, $rows['date_scrubber_format_day']->order);
		self::assertSame(26, $rows['date_scrubber_format_hour']->order);

		self::assertSame('j M Y H:00', $rows['timeline_photo_date_format_hour_multi_day']->value);
		self::assertSame(18, $rows['timeline_photo_date_format_hour_multi_day']->order);
	}

	public function testMultiDayHourFormatFlushesPhotoListings(): void
	{
		self::assertContains('timeline_photo_date_format_hour_multi_day', SettingsController::PHOTO_LISTING_COARSE_FLUSH_CONFIGS);
	}

	public function testFreeInstanceListsOnlyTheDayFormat(): void
	{
		$keys = $this->settingsKeys();

		self::assertContains(self::FREE_KEY, $keys);
		foreach (self::SE_KEYS as $key) {
			self::assertNotContains($key, $keys);
		}
	}

	public function testSupporterListsEveryFormat(): void
	{
		$this->requireSe();

		$keys = $this->settingsKeys();

		foreach ([...self::SE_KEYS, self::FREE_KEY] as $key) {
			self::assertContains($key, $keys);
		}
	}

	public function testFreeInstanceSavesTheDayFormat(): void
	{
		$this->assertOk($this->setConfig(self::FREE_KEY, 'j.n.'));

		self::assertSame('j.n.', Configs::query()->where('key', '=', self::FREE_KEY)->value('value'));
	}

	public function testFreeInstanceCannotSaveSupporterFormats(): void
	{
		foreach (self::SE_KEYS as $key) {
			$this->assertUnprocessable($this->setConfig($key, 'y'));
		}
	}

	public function testSupporterSavesEveryFormat(): void
	{
		$this->requireSe();

		$this->assertOk($this->setConfig('date_scrubber_format_hour', 'G\h'));

		self::assertSame('G\h', Configs::query()->where('key', '=', 'date_scrubber_format_hour')->value('value'));
	}

	public function testEmptyFormatIsRejected(): void
	{
		$this->assertUnprocessable($this->setConfig(self::FREE_KEY, ''));

		self::assertSame('d/m', Configs::query()->where('key', '=', self::FREE_KEY)->value('value'));
	}
}
