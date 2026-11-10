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

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Database\Migrations\Migration;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 095 (FR-095-07, S-095-08): the hour header and album day defaults
 * are replaced on rows that still hold them; customised values are kept.
 */
class TimelineDateFormatDefaultsMigrationTest extends BaseApiWithDataTest
{
	private const HOUR = 'timeline_photo_date_format_hour';
	private const ALBUM_DAY = 'timeline_album_date_format_day';

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach ([self::HOUR, self::ALBUM_DAY] as $key) {
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

	private function migration(): Migration
	{
		/** @var Migration $migration */
		$migration = require base_path('database/migrations/2026_10_16_000002_fix_timeline_date_format_defaults.php');

		return $migration;
	}

	private function valueOf(string $key): string
	{
		return strval(Configs::query()->where('key', '=', $key)->value('value'));
	}

	public function testOldDefaultsAreReplaced(): void
	{
		Configs::query()->where('key', '=', self::HOUR)->update(['value' => 'g:i']);
		Configs::query()->where('key', '=', self::ALBUM_DAY)->update(['value' => 'j M']);

		$this->migration()->up();

		self::assertSame('H:00', $this->valueOf(self::HOUR));
		self::assertSame('j M Y', $this->valueOf(self::ALBUM_DAY));
	}

	public function testCustomisedValuesAreKept(): void
	{
		Configs::query()->where('key', '=', self::HOUR)->update(['value' => 'h A']);
		Configs::query()->where('key', '=', self::ALBUM_DAY)->update(['value' => 'd.m.']);

		$this->migration()->up();

		self::assertSame('h A', $this->valueOf(self::HOUR));
		self::assertSame('d.m.', $this->valueOf(self::ALBUM_DAY));
	}

	public function testRollbackRestoresOldDefaults(): void
	{
		Configs::query()->where('key', '=', self::HOUR)->update(['value' => 'H:00']);
		Configs::query()->where('key', '=', self::ALBUM_DAY)->update(['value' => 'j M Y']);

		$this->migration()->down();

		self::assertSame('g:i', $this->valueOf(self::HOUR));
		self::assertSame('j M', $this->valueOf(self::ALBUM_DAY));
	}
}
