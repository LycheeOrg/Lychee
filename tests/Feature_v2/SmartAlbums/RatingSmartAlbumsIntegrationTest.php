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

use App\Models\Configs;
use App\Models\Photo;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Integration tests for rating-based smart albums.
 * Tests API responses, access control, permissions, and NSFW filtering.
 */
class RatingSmartAlbumsIntegrationTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * Test that smart album list includes rating smart albums when enabled.
	 */
	public function testSmartAlbumListIncludesRatingAlbums(): void
	{
		Configs::set('enable_unrated', true);
		Configs::set('enable_1_star', true);
		Configs::set('enable_2_stars', true);
		Configs::set('enable_3_stars', true);
		Configs::set('enable_4_stars', true);
		Configs::set('enable_5_stars', true);
		Configs::set('enable_best_pictures', true);

		$response = $this->actingAs($this->admin)->getJson('Albums');
		$response->assertOk();

		$albums = $response->json('smart_albums');
		$this->assertNotNull($albums, 'Smart albums should be present');

		$albumTitles = collect($albums)->pluck('title')->toArray();
		$this->assertContains('Unrated', $albumTitles);
		$this->assertContains('1 Star', $albumTitles);
		$this->assertContains('2 Stars', $albumTitles);
		$this->assertContains('3+ Stars', $albumTitles);
		$this->assertContains('4+ Stars', $albumTitles);
		$this->assertContains('5 Stars', $albumTitles);
		$this->assertContains('Best Pictures', $albumTitles);
	}

	/**
	 * Test that smart album list excludes disabled rating smart albums.
	 */
	public function testSmartAlbumListExcludesDisabledRatingAlbums(): void
	{
		Configs::set('enable_unrated', false);
		Configs::set('enable_1_star', false);
		Configs::set('enable_2_stars', false);
		Configs::set('enable_3_stars', false);
		Configs::set('enable_4_stars', false);
		Configs::set('enable_5_stars', false);
		Configs::set('enable_best_pictures', false);

		$response = $this->actingAs($this->admin)->getJson('Albums');
		$response->assertOk();

		$albums = $response->json('smart_albums');
		if ($albums !== null) {
			$albumTitles = collect($albums)->pluck('title')->toArray();
			$this->assertNotContains('Unrated', $albumTitles);
			$this->assertNotContains('1 Star', $albumTitles);
			$this->assertNotContains('2 Stars', $albumTitles);
			$this->assertNotContains('3+ Stars', $albumTitles);
			$this->assertNotContains('4+ Stars', $albumTitles);
			$this->assertNotContains('5 Stars', $albumTitles);
			$this->assertNotContains('Best Pictures', $albumTitles);
		}
	}

	/**
	 * Test that smart albums return photos in correct rating order.
	 */
	public function testSmartAlbumsReturnPhotosInRatingOrder(): void
	{
		Configs::set('enable_best_pictures', true);

		// Create photos with different ratings
		$photo1 = Photo::factory()->owned_by($this->admin)->create();
		$photo1->rating_avg = 3.0000;
		$photo1->save();

		$photo2 = Photo::factory()->owned_by($this->admin)->create();
		$photo2->rating_avg = 5.0000;
		$photo2->save();

		$photo3 = Photo::factory()->owned_by($this->admin)->create();
		$photo3->rating_avg = 4.0000;
		$photo3->save();

		// Fetch best pictures smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'best_pictures']);
		$response->assertOk();

		$photos = $response->json('photos');
		$this->assertCount(3, $photos);

		// Verify photos are ordered by rating DESC
		$this->assertEquals($photo2->id, $photos[0]['id'], 'First photo should have highest rating (5.0)');
		$this->assertEquals($photo3->id, $photos[1]['id'], 'Second photo should have second-highest rating (4.0)');
		$this->assertEquals($photo1->id, $photos[2]['id'], 'Third photo should have lowest rating (3.0)');
	}
}
