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

use App\Models\PhotoRating;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoRatingIntegrationTest extends BaseApiWithDataTest
{
	public function testSetRatingCreatesNewRating(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		$this->assertCreated($response);

		// Verify rating was created in database
		$this->assertDatabaseHas('photo_ratings', [
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 5,
		]);

		// Verify statistics were updated
		$this->assertDatabaseHas('statistics', [
			'photo_id' => $this->photo1->id,
			'rating_sum' => 5,
			'rating_count' => 1,
		]);
	}

	public function testSetRatingUpdatesExistingRating(): void
	{
		// Create initial rating
		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 3,
		]);

		// Update statistics manually (normally done by controller)
		$statistics = $this->photo1->statistics()->firstOrCreate(
			['photo_id' => $this->photo1->id],
			[
				'album_id' => null,
				'visit_count' => 0,
				'download_count' => 0,
				'favourite_count' => 0,
				'shared_count' => 0,
				'rating_sum' => 3,
				'rating_count' => 1,
			]
		);
		$statistics->rating_sum = 3;
		$statistics->rating_count = 1;
		$statistics->save();

		// Update rating to 5
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		$this->assertCreated($response);

		// Verify rating was updated
		$this->assertDatabaseHas('photo_ratings', [
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 5,
		]);

		// Verify statistics were updated (delta: +2)
		$statistics->refresh();
		$this->assertEquals(5, $statistics->rating_sum);
		$this->assertEquals(1, $statistics->rating_count);
	}

	public function testSetRatingZeroRemovesRating(): void
	{
		// Create initial rating
		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 4,
		]);

		// Set up statistics
		$statistics = $this->photo1->statistics()->firstOrCreate(
			['photo_id' => $this->photo1->id],
			[
				'album_id' => null,
				'visit_count' => 0,
				'download_count' => 0,
				'favourite_count' => 0,
				'shared_count' => 0,
				'rating_sum' => 4,
				'rating_count' => 1,
			]
		);
		$statistics->rating_sum = 4;
		$statistics->rating_count = 1;
		$statistics->save();

		// Remove rating
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		$this->assertCreated($response);

		// Verify rating was removed
		$this->assertDatabaseMissing('photo_ratings', [
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
		]);

		// Verify statistics were updated
		$statistics->refresh();
		$this->assertEquals(0, $statistics->rating_sum);
		$this->assertEquals(0, $statistics->rating_count);
	}

	public function testSetRatingZeroIsIdempotent(): void
	{
		// Remove rating that doesn't exist (should be idempotent)
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		$this->assertCreated($response);
	}

	public function testMultipleUsersCanRateSamePhoto(): void
	{
		// User 1 rates photo
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// User 2 rates same photo
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		// Verify both ratings exist
		$this->assertDatabaseHas('photo_ratings', [
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 5,
		]);

		$this->assertDatabaseHas('photo_ratings', [
			'photo_id' => $this->photo1->id,
			'user_id' => $this->admin->id,
			'rating' => 3,
		]);

		// Verify statistics aggregate
		$statistics = $this->photo1->statistics;
		$statistics->refresh();
		$this->assertEquals(8, $statistics->rating_sum); // 5 + 3
		$this->assertEquals(2, $statistics->rating_count);
	}
}
