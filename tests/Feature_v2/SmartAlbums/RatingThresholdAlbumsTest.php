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
 * Tests for rating threshold smart albums (Feature 009).
 *
 * Tests ThreeStarsAlbum+ (T-009-21), FourStarsAlbum+ (T-009-23), FiveStarsAlbum (T-009-25).
 * Verifies thresholds: 3★+ is [3.0, ∞), 4★+ is [4.0, ∞), 5★ is [5.0, 5.0] (S-009-07, S-009-08, S-009-09, S-009-16).
 */
class RatingThresholdAlbumsTest extends BaseApiWithDataTest
{
	/**
	 * T-009-21: Test that ThreeStarsAlbum contains photos with rating_avg >= 3.0.
	 */
	public function testThreeStarsAlbumContainsThreePlusStarPhotos(): void
	{
		Configs::set('enable_3_stars', '1');

		// photo1: 3.5 average (should be in 3★+)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		// photo2: 2.5 average (should NOT be in 3★+)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 2,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 3,
		]);

		// photo3: 4.5 average (should be in 3★+, also 4★+)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 4,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 5,
		]);

		// photo4: 5.0 average (should be in 3★+, 4★+, and 5★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo4->id,
			'rating' => 5,
		]);

		// Get three_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'three_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Verify photo1 (3.5 average) is present
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with 3.5 rating should be in ThreeStarsAlbum');

		// Verify photo2 (2.5 average) is NOT present
		$this->assertNotContains($this->photo2->id, $photoIds, 'Photo with 2.5 rating should NOT be in ThreeStarsAlbum');

		// Verify photo3 (4.5 average) is present
		$this->assertContains($this->photo3->id, $photoIds, 'Photo with 4.5 rating should be in ThreeStarsAlbum');

		// Verify photo4 (5.0 average) is present
		$this->assertContains($this->photo4->id, $photoIds, 'Photo with 5.0 rating should be in ThreeStarsAlbum');
	}

	/**
	 * T-009-21: Test boundary at exactly 3.0 (should be included in 3★+, S-009-16).
	 */
	public function testThreeStarsAlbumIncludesBoundary(): void
	{
		Configs::set('enable_3_stars', '1');

		// Create photo with exactly 3.0 rating
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		$this->photo1->refresh();
		$this->assertEquals('3.0000', $this->photo1->rating_avg);

		// Get three_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'three_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Photo with exactly 3.0 should be in 3★+ album
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with exactly 3.0 rating should be in ThreeStarsAlbum (boundary included)');
	}

	/**
	 * T-009-23: Test that FourStarsAlbum contains photos with rating_avg >= 4.0.
	 */
	public function testFourStarsAlbumContainsFourPlusStarPhotos(): void
	{
		Configs::set('enable_4_stars', '1');

		// photo1: 4.5 average (should be in 4★+)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// photo2: 3.5 average (should NOT be in 4★+)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 3,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 4,
		]);

		// photo3: 5.0 average (should be in 4★+)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 5,
		]);

		// Get four_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'four_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Verify photo1 (4.5 average) is present
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with 4.5 rating should be in FourStarsAlbum');

		// Verify photo2 (3.5 average) is NOT present
		$this->assertNotContains($this->photo2->id, $photoIds, 'Photo with 3.5 rating should NOT be in FourStarsAlbum');

		// Verify photo3 (5.0 average) is present
		$this->assertContains($this->photo3->id, $photoIds, 'Photo with 5.0 rating should be in FourStarsAlbum');
	}

	/**
	 * Test boundary at exactly 4.0 (should be included in 4★+).
	 */
	public function testFourStarsAlbumIncludesBoundary(): void
	{
		Configs::set('enable_4_stars', '1');

		// Create photo with exactly 4.0 rating
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg);

		// Get four_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'four_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Photo with exactly 4.0 should be in 4★+ album
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with exactly 4.0 rating should be in FourStarsAlbum (boundary included)');
	}

	/**
	 * T-009-25: Test that FiveStarsAlbum contains only photos with perfect 5.0 rating.
	 */
	public function testFiveStarsAlbumContainsOnlyPerfectRatings(): void
	{
		Configs::set('enable_5_stars', '1');

		// photo1: 5.0 average (should be in 5★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// photo2: 4.5 average (should NOT be in 5★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 4,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 5,
		]);

		// photo3: 5.0 average (multiple users all rated 5)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 5,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 5,
		]);

		// Get five_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'five_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Verify photo1 (5.0 average) is present
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with 5.0 rating should be in FiveStarsAlbum');

		// Verify photo2 (4.5 average) is NOT present
		$this->assertNotContains($this->photo2->id, $photoIds, 'Photo with 4.5 rating should NOT be in FiveStarsAlbum');

		// Verify photo3 (5.0 average) is present
		$this->assertContains($this->photo3->id, $photoIds, 'Photo with 5.0 rating from multiple users should be in FiveStarsAlbum');
	}

	/**
	 * Test that FiveStarsAlbum excludes photos with rating just below 5.0.
	 */
	public function testFiveStarsAlbumExcludesNearPerfect(): void
	{
		Configs::set('enable_5_stars', '1');

		// Create photo with less than 5.0 average
		// Three 5★ ratings + one 4★ rating = (5+5+5+4)/4 = 4.75
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->userMayUpload2)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		// Average: (5+5+4)/3 = 4.6667
		$this->photo1->refresh();
		$this->assertEquals('4.6667', $this->photo1->rating_avg);

		// Get five_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'five_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Photo with 4.6667 average should NOT be in 5★ album
		$this->assertNotContains($this->photo1->id, $photoIds, 'Photo with less than perfect 5.0 rating should NOT be in FiveStarsAlbum');
	}
}
