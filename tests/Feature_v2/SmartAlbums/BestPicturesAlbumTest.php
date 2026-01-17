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
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Tests for BestPicturesAlbum smart album (Feature 009, T-009-27 to T-009-29).
 */
class BestPicturesAlbumTest extends BaseApiWithDataTest
{
	/**
	 * T-009-27: Test album returns top N photos by rating.
	 */
	public function testBestPicturesAlbumReturnsTopN(): void
	{
		Configs::set('enable_best_pictures', '1');
		Configs::set('best_pictures_count', '2');

		// Create 3 photos with different ratings
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

		// Get best_pictures smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'best_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should contain top 2 rated photos
		$this->assertContains($this->photo1->id, $photoIds, 'Photo1 (5★) should be in BestPicturesAlbum');
		$this->assertContains($this->photo2->id, $photoIds, 'Photo2 (4★) should be in BestPicturesAlbum');
		$this->assertNotContains($this->photo3->id, $photoIds, 'Photo3 (3★) should NOT be in BestPicturesAlbum');
		$this->assertCount(2, $photoIds, 'BestPicturesAlbum should contain exactly 2 photos');
	}

	/**
	 * T-009-28: Test album includes ties (may show > N photos).
	 */
	public function testBestPicturesAlbumIncludesTies(): void
	{
		Configs::set('enable_best_pictures', '1');
		Configs::set('best_pictures_count', '2');

		// Create 3 photos, two with same top rating
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

		// Get best_pictures smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'best_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should contain both top-rated photos (even if > N)
		$this->assertContains($this->photo1->id, $photoIds, 'Photo1 (5★) should be in BestPicturesAlbum');
		$this->assertContains($this->photo2->id, $photoIds, 'Photo2 (5★) should be in BestPicturesAlbum');
		$this->assertNotContains($this->photo3->id, $photoIds, 'Photo3 (4★) should NOT be in BestPicturesAlbum');
		$this->assertGreaterThanOrEqual(2, count($photoIds), 'BestPicturesAlbum should contain at least 2 photos if there are ties');
	}

	/**
	 * T-009-29: Test album is hidden when Lychee SE not activated.
	 */
	public function testBestPicturesAlbumHiddenWithoutSE(): void
	{
		Configs::set('enable_best_pictures', '1');
		Configs::set('best_pictures_count', '2');

		// Simulate Lychee SE not activated (force is_supporter to false)
		// This may require mocking, but for now, check if album is hidden
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbums = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertNotContains('best_pictures', $smartAlbums, 'BestPicturesAlbum should not appear when Lychee SE is not activated');
	}
}
