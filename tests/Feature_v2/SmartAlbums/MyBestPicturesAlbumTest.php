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
 * Tests for MyBestPicturesAlbum smart album (Feature 011).
 */
class MyBestPicturesAlbumTest extends BaseApiWithDataTest
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
	 * S-011-02: Test top N with ties.
	 * With 2×5★ and 3×4★, limit=2 means cutoff is at 2nd photo (5★).
	 * So only photos with 5★ are included (2 photos total).
	 */
	public function testTopNWithTies(): void
	{
		Configs::set('enable_my_best_pictures', '1');
		Configs::set('my_best_pictures_count', '3');

		// Create photos and rate them: 2×5★, 3×4★
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 4,
		]);

		$photo4 = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $photo4->id,
			'rating' => 4,
		]);

		$photo5 = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $photo5->id,
			'rating' => 4,
		]);

		// Get my_best_pictures smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should contain both 5★ photos
		$this->assertContains($this->photo1->id, $photoIds, 'Photo1 (5★) should be in MyBestPicturesAlbum');
		$this->assertContains($this->photo2->id, $photoIds, 'Photo2 (5★) should be in MyBestPicturesAlbum');

		// Should contain all 4★ photos (tie at position 3)
		$this->assertContains($this->photo3->id, $photoIds, 'Photo3 (4★) should be in MyBestPicturesAlbum (tie)');
		$this->assertContains($photo4->id, $photoIds, 'Photo4 (4★) should be in MyBestPicturesAlbum (tie)');
		$this->assertContains($photo5->id, $photoIds, 'Photo5 (4★) should be in MyBestPicturesAlbum (tie)');

		// Total should be 5 (more than the configured 3, due to tie at cutoff)
		$this->assertEquals(5, count($photoIds), 'Album should contain 5 photos (2×5★ + 3×4★ tie at position 3)');
	}

	/**
	 * S-011-04: Test guest user cannot see album (hidden from list).
	 */
	public function testGuestUserCannotSeeAlbum(): void
	{
		Configs::set('enable_my_best_pictures', '1');

		// Get smart albums as guest
		$response = $this->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();

		// My Best Pictures should not appear for guests
		$this->assertNotContains('my_best_pictures', $smartAlbumIds, 'MyBestPicturesAlbum should NOT appear for guest users');
	}

	/**
	 * S-011-07: Test all same rating, all included.
	 */
	public function testAllSameRatingAllIncluded(): void
	{
		Configs::set('enable_my_best_pictures', '1');
		Configs::set('my_best_pictures_count', '2');

		// Rate 5 photos all with 5 stars
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 5,
		]);

		$photo4 = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $photo4->id,
			'rating' => 5,
		]);

		$photo5 = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $photo5->id,
			'rating' => 5,
		]);

		// Get album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// All 5 photos should be included (even though limit is 2)
		$this->assertCount(5, $photoIds, 'All 5 photos with same rating should be included');
		$this->assertContains($this->photo1->id, $photoIds);
		$this->assertContains($this->photo2->id, $photoIds);
		$this->assertContains($this->photo3->id, $photoIds);
		$this->assertContains($photo4->id, $photoIds);
		$this->assertContains($photo5->id, $photoIds);
	}

	/**
	 * S-011-08: Test exact N photos, no ties.
	 */
	public function testExactNPhotosNoTies(): void
	{
		Configs::set('enable_my_best_pictures', '1');
		Configs::set('my_best_pictures_count', '2');

		// Rate 3 photos with distinct ratings
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 4,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 3,
		]);

		// Get album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should contain exactly 2 photos (top rated)
		$this->assertCount(2, $photoIds, 'Album should contain exactly 2 photos');
		$this->assertContains($this->photo1->id, $photoIds, 'Photo1 (5★) should be included');
		$this->assertContains($this->photo2->id, $photoIds, 'Photo2 (4★) should be included');
		$this->assertNotContains($this->photo3->id, $photoIds, 'Photo3 (3★) should NOT be included');
	}

	/**
	 * S-011-09: Test tie at cutoff, all included.
	 * 8×5★, 15×4★, limit=10 → should show 8×5★ + all 15×4★ = 23 total.
	 */
	public function testTieAtCutoffAllIncluded(): void
	{
		Configs::set('enable_my_best_pictures', '1');
		Configs::set('my_best_pictures_count', '10');

		// Create 8 photos with 5★
		$fiveStarPhotos = [];
		for ($i = 0; $i < 8; $i++) {
			$photo = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();
			$this->actingAs($this->admin)->postJson('Photo::setRating', [
				'photo_id' => $photo->id,
				'rating' => 5,
			]);
			$fiveStarPhotos[] = $photo->id;
		}

		// Create 15 photos with 4★
		$fourStarPhotos = [];
		for ($i = 0; $i < 15; $i++) {
			$photo = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();
			$this->actingAs($this->admin)->postJson('Photo::setRating', [
				'photo_id' => $photo->id,
				'rating' => 4,
			]);
			$fourStarPhotos[] = $photo->id;
		}

		// Create 5 photos with 3★ (should not be included)
		$threeStarPhotos = [];
		for ($i = 0; $i < 5; $i++) {
			$photo = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();
			$this->actingAs($this->admin)->postJson('Photo::setRating', [
				'photo_id' => $photo->id,
				'rating' => 3,
			]);
			$threeStarPhotos[] = $photo->id;
		}

		// Get album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should contain all 8×5★ + all 15×4★ = 23 photos
		$this->assertEquals(23, count($photoIds), 'Album should contain 23 photos (8×5★ + 15×4★)');

		// Verify all 5★ photos are included
		foreach ($fiveStarPhotos as $photoId) {
			$this->assertContains($photoId, $photoIds, "5★ photo $photoId should be included");
		}

		// Verify all 4★ photos are included
		foreach ($fourStarPhotos as $photoId) {
			$this->assertContains($photoId, $photoIds, "4★ photo $photoId should be included (tie)");
		}

		// Verify 3★ photos are NOT included
		foreach ($threeStarPhotos as $photoId) {
			$this->assertNotContains($photoId, $photoIds, "3★ photo $photoId should NOT be included");
		}
	}

	/**
	 * S-011-11: Test no SE license, album disabled.
	 */
	public function testNoSeLicenseAlbumDisabled(): void
	{
		// Reset SE license
		$this->resetSe();

		Configs::set('enable_my_best_pictures', '1');

		// Get smart albums without SE license
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();

		// My Best Pictures should NOT appear without SE license
		$this->assertNotContains('my_best_pictures', $smartAlbumIds, 'MyBestPicturesAlbum should NOT appear without SE license');
	}
}
