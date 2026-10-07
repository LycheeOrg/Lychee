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

namespace Tests\Feature_v3\Insights;

use App\Repositories\ConfigManager;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers the calendar, rhythm, devices and exposure figures of
 * `GET /api/v3/Insights` (Feature 085, FR-085-10 … FR-085-13, FR-085-15,
 * S-085-12, S-085-15, S-085-18).
 */
class InsightsAggregateChartsTest extends BaseApiWithDataTest
{
	use InsightsDataset;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->seedInsightsDataset();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testLibraryCalendarCountsLocalDays(): void
	{
		$response = $this->insights(['period' => 'library']);

		$response->assertJsonPath('calendar.dates', ['2024-06-15', '2025-01-01', '2025-01-02', '2025-03-10']);
		$response->assertJsonPath('calendar.counts', [1, 1, 2, 1]);
		$config_manager = resolve(ConfigManager::class);
		$response->assertJsonPath('calendar.low', $config_manager->getValueAsInt('low_number_of_shoots_per_day'));
		$response->assertJsonPath('calendar.high', $config_manager->getValueAsInt('high_number_of_shoots_per_day'));
	}

	public function testYearCalendarCountsLocalDays(): void
	{
		$response = $this->insights(['period' => 'year', 'year' => 2025]);

		$response->assertJsonPath('calendar.dates', ['2025-01-01', '2025-01-02', '2025-03-10']);
		$response->assertJsonPath('calendar.counts', [1, 2, 1]);
	}

	public function testRhythmUsesLocalTime(): void
	{
		$response = $this->insights(['period' => 'library']);

		// p1 is stored at 23:30 UTC on a Tuesday but taken at 08:30 on Wednesday.
		$response->assertJsonPath('rhythm.week_hour.2.8', 1);
		$response->assertJsonPath('rhythm.week_hour.1.23', 0);
		$response->assertJsonPath('rhythm.weekdays', [1, 0, 1, 2, 0, 1, 0]);
		$response->assertJsonPath('rhythm.months', [3, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0]);
		$hours = array_fill(0, 24, 0);
		foreach ([8, 9, 10, 12, 18] as $hour) {
			$hours[$hour] = 1;
		}
		$response->assertJsonPath('rhythm.hours', $hours);
	}

	public function testDevicesAreNormalisedAndCounted(): void
	{
		$response = $this->insights(['period' => 'library']);

		$response->assertJsonPath('devices.devices.0', [
			'name' => 'Nikon D850',
			'category' => 'camera',
			'all' => 3,
			'photos' => 3,
			'videos' => 0,
			'highlighted' => 1,
			'located' => 1,
			'with_people' => 1,
		]);
		$response->assertJsonPath('devices.devices.1', [
			'name' => 'Apple iPhone 13 Pro',
			'category' => 'mobile',
			'all' => 2,
			'photos' => 1,
			'videos' => 1,
			'highlighted' => 0,
			'located' => 1,
			'with_people' => 1,
		]);
		$response->assertJsonPath('devices.devices.2.name', null);
		$response->assertJsonPath('devices.devices.2.category', 'other');
		$response->assertJsonCount(3, 'devices.devices');

		$response->assertJsonPath('devices.manufacturers.0.name', 'Nikon');
		$response->assertJsonPath('devices.manufacturers.0.all', 3);
		$response->assertJsonPath('devices.manufacturers.1.name', 'Apple');

		$response->assertJsonPath('devices.lenses.0.name', 'AF-S 24-70mm');
		$response->assertJsonPath('devices.lenses.0.all', 3);
		$response->assertJsonCount(4, 'devices.lenses');
	}

	public function testFocalLengthsPerDevice(): void
	{
		$response = $this->insights(['period' => 'library']);

		// Videos and items without a focal length are left out; most used device first.
		$response->assertJsonPath('devices.focal_lengths.0.name', 'Nikon D850');
		$response->assertJsonPath('devices.focal_lengths.0.category', 'camera');
		$response->assertJsonPath('devices.focal_lengths.0.values', [24, 70]);
		$response->assertJsonPath('devices.focal_lengths.0.counts', [1, 1]);
		$response->assertJsonPath('devices.focal_lengths.1.name', 'Apple iPhone 13 Pro');
		$response->assertJsonPath('devices.focal_lengths.1.values', [5.7]);
		$response->assertJsonCount(2, 'devices.focal_lengths');
	}

	public function testYearLeavesUndatedPhotosOutOfDevices(): void
	{
		$response = $this->insights(['period' => 'year', 'year' => 2025]);

		// Equal counts are ordered by name.
		$response->assertJsonPath('devices.devices.0.name', 'Apple iPhone 13 Pro');
		$response->assertJsonPath('devices.devices.0.all', 2);
		$response->assertJsonPath('devices.devices.1.name', 'Nikon D850');
		$response->assertJsonPath('devices.devices.1.all', 2);
		$response->assertJsonCount(2, 'devices.devices');
	}

	public function testExposureDistributions(): void
	{
		$response = $this->insights(['period' => 'library']);

		$response->assertJsonPath('exposure.iso.values', [100, 400, 3200]);
		$response->assertJsonPath('exposure.iso.counts', [2, 1, 1]);
		$response->assertJsonPath('exposure.iso.total', 4);
		$response->assertJsonPath('exposure.iso.excluded', 1);
		$response->assertJsonPath('exposure.iso.mode', 100);
		$response->assertJsonPath('exposure.iso.median', 250);

		$response->assertJsonPath('exposure.aperture.values', [1.5, 2.8, 4]);
		$response->assertJsonPath('exposure.aperture.excluded', 2);
		$response->assertJsonPath('exposure.focal.values', [5.7, 24, 70]);
		$response->assertJsonPath('exposure.focal.excluded', 2);
		$response->assertJsonCount(3, 'exposure.shutter.values');
		self::assertEqualsWithDelta(1 / 250, $response->json('exposure.shutter.min'), 0.000001);
		self::assertEqualsWithDelta(1 / 60, $response->json('exposure.shutter.max'), 0.000001);
		$response->assertJsonPath('exposure.shutter.excluded', 2);

		$response->assertJsonPath('exposure.video_length.values', [20]);
		$response->assertJsonPath('exposure.video_length.excluded', 0);
		self::assertEqualsWithDelta(20.4, $response->json('exposure.total_video_duration'), 0.0001);
	}

	/**
	 * @param array<string,mixed> $data
	 */
	private function insights(array $data): TestResponse
	{
		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', $data);
		$this->assertOk($response);

		return $response;
	}
}
