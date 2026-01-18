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

namespace Tests\Feature_v2\Photo;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Tests for rating_avg synchronization on photos table (Feature 009).
 *
 * Verifies that photo.rating_avg is correctly updated when:
 * - A new rating is created (T-009-03)
 * - An existing rating is updated (T-009-04)
 * - A rating is removed (T-009-05)
 */
class PhotoRatingSyncTest extends BaseApiWithDataTest
{
	/**
	 * T-009-03: Test that creating a new rating updates photo.rating_avg.
	 */
	public function testNewRatingSyncsRatingAvg(): void
	{
		// Verify photo starts with no rating_avg
		$this->photo1->refresh();
		$this->assertNull($this->photo1->rating_avg);

		// User rates the photo
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		$this->assertCreated($response);

		// Verify rating_avg was synced to photo
		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg);
	}

	/**
	 * T-009-03: Test rating_avg calculation with multiple users.
	 */
	public function testMultipleRatingsSyncRatingAvg(): void
	{
		// User 1 rates the photo 5
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		$this->photo1->refresh();
		$this->assertEquals('5.0000', $this->photo1->rating_avg);

		// User 2 (admin) rates the same photo 3
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		// Average should be (5 + 3) / 2 = 4.0
		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg);
	}

	/**
	 * T-009-04: Test that updating an existing rating updates photo.rating_avg.
	 */
	public function testUpdatingRatingSyncsRatingAvg(): void
	{
		// Create initial rating
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		$this->photo1->refresh();
		$this->assertEquals('3.0000', $this->photo1->rating_avg);

		// Update rating to 5
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		$this->assertCreated($response);

		// Verify rating_avg was updated
		$this->photo1->refresh();
		$this->assertEquals('5.0000', $this->photo1->rating_avg);
	}

	/**
	 * T-009-04: Test updating one user's rating when multiple ratings exist.
	 */
	public function testUpdatingOneRatingWithMultipleUsers(): void
	{
		// User 1 rates 5, User 2 rates 3
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg); // (5+3)/2

		// User 1 updates their rating from 5 to 1
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 1,
		]);

		// Average should be (1 + 3) / 2 = 2.0
		$this->photo1->refresh();
		$this->assertEquals('2.0000', $this->photo1->rating_avg);
	}

	/**
	 * T-009-05: Test that removing a rating sets photo.rating_avg to NULL.
	 */
	public function testRemovingRatingSetsRatingAvgToNull(): void
	{
		// Create initial rating
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg);

		// Remove rating (rating = 0)
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		$this->assertCreated($response);

		// Verify rating_avg is now NULL
		$this->photo1->refresh();
		$this->assertNull($this->photo1->rating_avg);
	}

	/**
	 * T-009-05: Test removing one user's rating when multiple ratings exist.
	 */
	public function testRemovingOneRatingWithMultipleUsers(): void
	{
		// User 1 rates 5, User 2 rates 3
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg); // (5+3)/2

		// User 1 removes their rating
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		// Average should be 3 (only admin's rating remains)
		$this->photo1->refresh();
		$this->assertEquals('3.0000', $this->photo1->rating_avg);
	}

	/**
	 * T-009-05: Test removing all ratings sets rating_avg to NULL.
	 */
	public function testRemovingAllRatingsSetsRatingAvgToNull(): void
	{
		// Two users rate the photo
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		// Both users remove their ratings
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		// Verify rating_avg is NULL
		$this->photo1->refresh();
		$this->assertNull($this->photo1->rating_avg);
	}

	/**
	 * Test rating_avg precision for fractional averages.
	 */
	public function testRatingAvgPrecision(): void
	{
		// Create ratings that result in fractional average: (5+4+3)/3 = 4.0
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);
		$this->actingAs($this->userMayUpload2)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		// Average: (5+4+3)/3 = 4.0
		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg);
	}

	/**
	 * Test rating_avg precision with non-integer average.
	 */
	public function testRatingAvgFractionalPrecision(): void
	{
		// Create ratings: (5+3)/2 = 4.0 (integer result)
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		$this->photo1->refresh();
		$this->assertEquals('4.0000', $this->photo1->rating_avg);

		// Add another rating: (5+3+1)/3 = 3.0
		$this->actingAs($this->userMayUpload2)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 1,
		]);

		$this->photo1->refresh();
		$this->assertEquals('3.0000', $this->photo1->rating_avg);
	}

	/**
	 * Test rating_avg with repeating decimal: (5+4)/2 = 4.5.
	 */
	public function testRatingAvgHalfPrecision(): void
	{
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		$this->photo1->refresh();
		$this->assertEquals('4.5000', $this->photo1->rating_avg);
	}

	/**
	 * Test that removing non-existent rating is idempotent (no effect on rating_avg).
	 */
	public function testRemovingNonExistentRatingIsIdempotent(): void
	{
		// Verify photo has no rating_avg
		$this->photo1->refresh();
		$this->assertNull($this->photo1->rating_avg);

		// Try to remove rating that doesn't exist
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		$this->assertCreated($response);

		// Verify rating_avg is still NULL
		$this->photo1->refresh();
		$this->assertNull($this->photo1->rating_avg);
	}
}
