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
 * Tests for rating bucket smart albums (Feature 009).
 *
 * Tests OneStarAlbum (T-009-17), TwoStarsAlbum (T-009-19).
 * Verifies bucket boundaries: 1★ is [1.0, 2.0), 2★ is [2.0, 3.0) (S-009-05, S-009-06, S-009-17).
 */
class RatingBucketAlbumsTest extends BaseApiWithDataTest
{
	/**
	 * T-009-17: Test that OneStarAlbum contains photos with 1.0 <= rating_avg < 2.0.
	 */
	public function testOneStarAlbumContainsOnlyOneStarPhotos(): void
	{
		Configs::set('enable_1_star', '1');

		// Create photos with different ratings
		// photo1: 1.5 average (should be in 1★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 1,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 2,
		]);

		// photo2: 2.5 average (should NOT be in 1★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 2,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 3,
		]);

		// photo3: unrated (should NOT be in 1★)
		// Don't rate photo3 at all

		// Get one_star smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'one_star']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Verify photo1 (1.5 average) is present
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with 1.5 rating should be in OneStarAlbum');

		// Verify photo2 (2.5 average) is NOT present
		$this->assertNotContains($this->photo2->id, $photoIds, 'Photo with 2.5 rating should NOT be in OneStarAlbum');

		// Verify photo3 (unrated) is NOT present
		$this->assertNotContains($this->photo3->id, $photoIds, 'Unrated photo should NOT be in OneStarAlbum');
	}

	/**
	 * T-009-17: Test boundary at exactly 2.0 (should be excluded from 1★, S-009-17).
	 */
	public function testOneStarAlbumExcludesTwoStarBoundary(): void
	{
		Configs::set('enable_1_star', '1');

		// Create photo with exactly 2.0 rating
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 2,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 2,
		]);

		$this->photo1->refresh();
		$this->assertEquals('2.0000', $this->photo1->rating_avg);

		// Get one_star smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'one_star']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Photo with exactly 2.0 should NOT be in 1★ album
		$this->assertNotContains($this->photo1->id, $photoIds, 'Photo with exactly 2.0 rating should NOT be in OneStarAlbum (boundary excluded)');
	}

	/**
	 * Test boundary at exactly 1.0 (should be included in 1★).
	 */
	public function testOneStarAlbumIncludesLowerBoundary(): void
	{
		Configs::set('enable_1_star', '1');

		// Create photo with exactly 1.0 rating
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 1,
		]);

		$this->photo1->refresh();
		$this->assertEquals('1.0000', $this->photo1->rating_avg);

		// Get one_star smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'one_star']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Photo with exactly 1.0 should be in 1★ album
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with exactly 1.0 rating should be in OneStarAlbum (boundary included)');
	}

	/**
	 * T-009-19: Test that TwoStarsAlbum contains photos with 2.0 <= rating_avg < 3.0.
	 */
	public function testTwoStarsAlbumContainsOnlyTwoStarPhotos(): void
	{
		Configs::set('enable_2_stars', '1');

		// photo1: 2.5 average (should be in 2★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 2,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		// photo2: 1.5 average (should NOT be in 2★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 1,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 2,
		]);

		// photo3: 3.5 average (should NOT be in 2★)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 3,
		]);
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 4,
		]);

		// Get two_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'two_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Verify photo1 (2.5 average) is present
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with 2.5 rating should be in TwoStarsAlbum');

		// Verify photo2 (1.5 average) is NOT present
		$this->assertNotContains($this->photo2->id, $photoIds, 'Photo with 1.5 rating should NOT be in TwoStarsAlbum');

		// Verify photo3 (3.5 average) is NOT present
		$this->assertNotContains($this->photo3->id, $photoIds, 'Photo with 3.5 rating should NOT be in TwoStarsAlbum');
	}

	/**
	 * Test boundary at exactly 2.0 (should be included in 2★).
	 */
	public function testTwoStarsAlbumIncludesLowerBoundary(): void
	{
		Configs::set('enable_2_stars', '1');

		// Create photo with exactly 2.0 rating
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 2,
		]);

		$this->photo1->refresh();
		$this->assertEquals('2.0000', $this->photo1->rating_avg);

		// Get two_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'two_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Photo with exactly 2.0 should be in 2★ album
		$this->assertContains($this->photo1->id, $photoIds, 'Photo with exactly 2.0 rating should be in TwoStarsAlbum (boundary included)');
	}

	/**
	 * Test boundary at exactly 3.0 (should be excluded from 2★).
	 */
	public function testTwoStarsAlbumExcludesUpperBoundary(): void
	{
		Configs::set('enable_2_stars', '1');

		// Create photo with exactly 3.0 rating
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		$this->photo1->refresh();
		$this->assertEquals('3.0000', $this->photo1->rating_avg);

		// Get two_stars smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'two_stars']);
		$this->assertOk($response);

		$photoIds = collect($response->json('resource.photos'))->pluck('id')->all();

		// Photo with exactly 3.0 should NOT be in 2★ album
		$this->assertNotContains($this->photo1->id, $photoIds, 'Photo with exactly 3.0 rating should NOT be in TwoStarsAlbum (boundary excluded)');
	}
}
