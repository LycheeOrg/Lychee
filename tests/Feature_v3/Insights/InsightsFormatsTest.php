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

use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers the image-format figures of `GET /api/v3/Insights`
 * (Feature 085, FR-085-17, S-085-24).
 */
class InsightsFormatsTest extends BaseApiWithDataTest
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

	public function testOrientationPerMedia(): void
	{
		$response = $this->insights(['period' => 'library']);

		$response->assertJsonPath('formats.all', ['portrait' => 1, 'landscape' => 3, 'square' => 1, 'unknown' => 1]);
		$response->assertJsonPath('formats.photos', ['portrait' => 1, 'landscape' => 2, 'square' => 1, 'unknown' => 0]);
		$response->assertJsonPath('formats.videos', ['portrait' => 0, 'landscape' => 1, 'square' => 0, 'unknown' => 0]);
	}

	public function testAspectRatioGroupsIgnoreOrientation(): void
	{
		$response = $this->insights(['period' => 'library']);

		$groups = collect($response->json('formats.aspect_ratios'))->mapWithKeys(fn (array $group) => [$group['group'] => $group['count']])->all();
		self::assertSame(['1:1' => 1, '5:4' => 0, '4:3' => 1, '3:2' => 2, '16:9' => 1, '2:1' => 0, 'panorama' => 0, 'other' => 0], $groups);
	}

	public function testMostFrequentDimensions(): void
	{
		$response = $this->insights(['period' => 'library']);

		// Equal counts: larger frames first.
		$response->assertJsonPath('formats.dimensions.widths', [6000, 4000, 4032, 3000, 1920]);
		$response->assertJsonPath('formats.dimensions.heights', [4000, 6000, 3024, 3000, 1080]);
		$response->assertJsonPath('formats.dimensions.counts', [1, 1, 1, 1, 1]);
		$response->assertJsonPath('formats.dimensions.formats', 5);
		$response->assertJsonPath('formats.dimensions.with_dimensions', 5);
	}

	public function testPeriodFiltersFormats(): void
	{
		$response = $this->insights(['period' => 'year', 'year' => 2024]);

		$response->assertJsonPath('formats.all', ['portrait' => 0, 'landscape' => 0, 'square' => 0, 'unknown' => 1]);
		$response->assertJsonPath('formats.dimensions.formats', 0);
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
