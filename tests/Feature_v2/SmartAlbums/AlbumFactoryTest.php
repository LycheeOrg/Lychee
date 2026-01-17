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

namespace Tests\Feature_v2\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Factories\AlbumFactory;
use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Tests for AlbumFactory smart album registration (Feature 009, T-009-31).
 */
class AlbumFactoryTest extends BaseApiWithDataTest
{
	/**
	 * T-009-31: Test getAllBuiltInSmartAlbums() returns new albums when enabled.
	 */
	public function testGetAllBuiltInSmartAlbumsReturnsNewAlbums(): void
	{
		// Enable all rating smart albums
		Configs::set('enable_unrated', '1');
		Configs::set('enable_1_star', '1');
		Configs::set('enable_2_stars', '1');
		Configs::set('enable_3_stars', '1');
		Configs::set('enable_4_stars', '1');
		Configs::set('enable_5_stars', '1');
		Configs::set('enable_best_pictures', '1');

		$factory = resolve(AlbumFactory::class);
		$albums = $factory->getAllBuiltInSmartAlbums();
		$albumIds = collect($albums)->pluck('id')->all();

		// Use API to get smart albums
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);
		$smartAlbums = collect($response->json('smart_albums'))->pluck('id')->all();

		// Check that all new smart album IDs are present
		foreach ([
			SmartAlbumType::UNRATED->value,
			SmartAlbumType::ONE_STAR->value,
			SmartAlbumType::TWO_STARS->value,
			SmartAlbumType::THREE_STARS->value,
			SmartAlbumType::FOUR_STARS->value,
			SmartAlbumType::FIVE_STARS->value,
		] as $id) {
			$this->assertContains($id, $smartAlbums, "Smart album $id should be registered and enabled");
		}
		// Optionally check best_pictures if SE is active
		if (resolve('config')->get('lychee.se_active', false)) {
			$this->assertContains(SmartAlbumType::BEST_PICTURES->value, $smartAlbums, 'Smart album best_pictures should be registered and enabled');
		}

		// Refresh configs to ensure changes are picked up
		app()->forgetInstance(AlbumFactory::class);
		$factory = resolve(AlbumFactory::class);
	}
}
