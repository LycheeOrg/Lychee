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
	/**
	 * Test that smart album list includes rating smart albums when enabled.
	 */
	public function testSmartAlbumListIncludesRatingAlbums(): void
	{
		Configs::set('smart_albums_unrated_enabled', true);
		Configs::set('smart_albums_one_star_enabled', true);
		Configs::set('smart_albums_two_stars_enabled', true);
		Configs::set('smart_albums_three_stars_enabled', true);
		Configs::set('smart_albums_four_stars_enabled', true);
		Configs::set('smart_albums_five_stars_enabled', true);
		Configs::set('smart_albums_best_pictures_enabled', true);

		$response = $this->actingAs($this->admin)->getJson('/api/v2/gallery/albums');
		$response->assertOk();

		$albums = $response->json('data.smart_albums');
		$this->assertNotNull($albums, 'Smart albums should be present');

		$albumTitles = collect($albums)->pluck('title')->toArray();
		$this->assertContains('Unrated', $albumTitles);
		$this->assertContains('1 Star', $albumTitles);
		$this->assertContains('2 Stars', $albumTitles);
		$this->assertContains('3 Stars', $albumTitles);
		$this->assertContains('4 Stars', $albumTitles);
		$this->assertContains('5 Stars', $albumTitles);
		$this->assertContains('Best Pictures', $albumTitles);
	}

	/**
	 * Test that smart album list excludes disabled rating smart albums.
	 */
	public function testSmartAlbumListExcludesDisabledRatingAlbums(): void
	{
		Configs::set('smart_albums_unrated_enabled', false);
		Configs::set('smart_albums_one_star_enabled', false);
		Configs::set('smart_albums_two_stars_enabled', false);
		Configs::set('smart_albums_three_stars_enabled', false);
		Configs::set('smart_albums_four_stars_enabled', false);
		Configs::set('smart_albums_five_stars_enabled', false);
		Configs::set('smart_albums_best_pictures_enabled', false);

		$response = $this->actingAs($this->admin)->getJson('/api/v2/gallery/albums');
		$response->assertOk();

		$albums = $response->json('data.smart_albums');
		if ($albums !== null) {
			$albumTitles = collect($albums)->pluck('title')->toArray();
			$this->assertNotContains('Unrated', $albumTitles);
			$this->assertNotContains('1 Star', $albumTitles);
			$this->assertNotContains('2 Stars', $albumTitles);
			$this->assertNotContains('3 Stars', $albumTitles);
			$this->assertNotContains('4 Stars', $albumTitles);
			$this->assertNotContains('5 Stars', $albumTitles);
			$this->assertNotContains('Best Pictures', $albumTitles);
		}
	}

	/**
	 * Test that public users can access rating smart albums when public smart albums are enabled.
	 */
	public function testPublicCanAccessRatingSmartAlbumsWhenEnabled(): void
	{
		Configs::set('public_smart_albums', true);
		Configs::set('smart_albums_one_star_enabled', true);

		// Create a public photo with 1-star rating
		$photo = Photo::factory()->owned_by($this->admin)->create([
			'is_public' => true,
		]);
		$photo->rating_avg = 1.0000;
		$photo->save();

		// Guest should be able to see 1-star smart album
		$response = $this->getJson('/api/v2/gallery/albums');
		$response->assertOk();

		$albums = $response->json('data.smart_albums');
		if ($albums !== null) {
			$albumTitles = collect($albums)->pluck('title')->toArray();
			$this->assertContains('1 Star', $albumTitles);
		}
	}

	/**
	 * Test that public users cannot access rating smart albums when public smart albums are disabled.
	 */
	public function testPublicCannotAccessRatingSmartAlbumsWhenDisabled(): void
	{
		Configs::set('public_smart_albums', false);
		Configs::set('smart_albums_one_star_enabled', true);

		// Create a public photo with 1-star rating
		$photo = Photo::factory()->owned_by($this->admin)->create([
			'is_public' => true,
		]);
		$photo->rating_avg = 1.0000;
		$photo->save();

		// Guest should NOT see smart albums
		$response = $this->getJson('/api/v2/gallery/albums');
		$response->assertOk();

		$albums = $response->json('data.smart_albums');
		$this->assertNull($albums, 'Smart albums should not be visible to guests when public_smart_albums is disabled');
	}

	/**
	 * Test that smart albums respect photo access permissions.
	 */
	public function testSmartAlbumsRespectPhotoAccessPermissions(): void
	{
		Configs::set('smart_albums_one_star_enabled', true);

		// Create a private photo with 1-star rating owned by admin
		$privatePhoto = Photo::factory()->owned_by($this->admin)->create([
			'is_public' => false,
		]);
		$privatePhoto->rating_avg = 1.0000;
		$privatePhoto->save();

		// User1 should not see the private photo in smart album
		$response = $this->actingAs($this->user1)->getJson('/api/v2/smart-album/one-star');
		$response->assertOk();

		$photos = $response->json('data.photos');
		$photoIds = collect($photos)->pluck('id')->toArray();
		$this->assertNotContains($privatePhoto->id, $photoIds, 'User1 should not see admin\'s private photo');

		// Admin should see the private photo in smart album
		$response = $this->actingAs($this->admin)->getJson('/api/v2/smart-album/one-star');
		$response->assertOk();

		$photos = $response->json('data.photos');
		$photoIds = collect($photos)->pluck('id')->toArray();
		$this->assertContains($privatePhoto->id, $photoIds, 'Admin should see their own private photo');
	}

	/**
	 * Test that smart albums respect NSFW filtering.
	 */
	public function testSmartAlbumsRespectNsfwFiltering(): void
	{
		Configs::set('smart_albums_one_star_enabled', true);
		Configs::set('nsfw_visible', false);

		// Create an NSFW photo with 1-star rating
		$nsfwPhoto = Photo::factory()->owned_by($this->admin)->create([
			'is_nsfw' => true,
			'is_public' => true,
		]);
		$nsfwPhoto->rating_avg = 1.0000;
		$nsfwPhoto->save();

		// Guest should NOT see NSFW photo when nsfw_visible is false
		$response = $this->getJson('/api/v2/smart-album/one-star');
		$response->assertOk();

		$photos = $response->json('data.photos');
		if ($photos !== null) {
			$photoIds = collect($photos)->pluck('id')->toArray();
			$this->assertNotContains($nsfwPhoto->id, $photoIds, 'NSFW photo should not be visible when nsfw_visible is false');
		}

		// Enable NSFW visibility
		Configs::set('nsfw_visible', true);

		// Guest should now see NSFW photo
		$response = $this->getJson('/api/v2/smart-album/one-star');
		$response->assertOk();

		$photos = $response->json('data.photos');
		$photoIds = collect($photos)->pluck('id')->toArray();
		$this->assertContains($nsfwPhoto->id, $photoIds, 'NSFW photo should be visible when nsfw_visible is true');
	}

	/**
	 * Test that smart albums return photos in correct rating order.
	 */
	public function testSmartAlbumsReturnPhotosInRatingOrder(): void
	{
		Configs::set('smart_albums_best_pictures_enabled', true);

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
		$response = $this->actingAs($this->admin)->getJson('/api/v2/smart-album/best-pictures');
		$response->assertOk();

		$photos = $response->json('data.photos');
		$this->assertCount(3, $photos);

		// Verify photos are ordered by rating DESC
		$this->assertEquals($photo2->id, $photos[0]['id'], 'First photo should have highest rating (5.0)');
		$this->assertEquals($photo3->id, $photos[1]['id'], 'Second photo should have second-highest rating (4.0)');
		$this->assertEquals($photo1->id, $photos[2]['id'], 'Third photo should have lowest rating (3.0)');
	}
}
