<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

use App\Exceptions\ModelDBException;
use App\Models\PhotoRating;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoRatingConcurrencyTest extends BaseApiWithDataTest
{
	/**
	 * Test that the unique constraint prevents duplicate rating records.
	 * Attempts to insert duplicate (photo_id, user_id) pairs should fail.
	 */
	public function testUniqueConstraintPreventsDuplicateRatings(): void
	{
		if (DB::getDriverName() === 'pgsql') {
			$this->markTestSkipped('This test is only relevant for SQLite databases.');

			return;
		}

		// Create initial rating
		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 3,
		]);

		// Attempt to insert duplicate should throw exception
		$this->expectException(ModelDBException::class);

		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 5,
		]);
	}

	/**
	 * Test that rapidly updating a rating (same user) works correctly.
	 * The last write should win, and statistics should reflect final state.
	 */
	public function testSameUserRapidRatingUpdates(): void
	{
		// Rapidly submit 5 rating updates
		$ratings = [5, 3, 4, 2, 1];

		foreach ($ratings as $rating) {
			$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
				'photo_id' => $this->photo1->id,
				'rating' => $rating,
			]);
			$this->assertCreated($response);
		}

		// Verify only one rating record exists
		$ratingCount = PhotoRating::where('photo_id', $this->photo1->id)
			->where('user_id', $this->userMayUpload1->id)
			->count();
		$this->assertEquals(1, $ratingCount);

		// Verify final rating is the last one submitted
		$finalRating = PhotoRating::where('photo_id', $this->photo1->id)
			->where('user_id', $this->userMayUpload1->id)
			->value('rating');
		$this->assertEquals(1, $finalRating);

		// Verify statistics are consistent (sum = 1, count = 1)
		$statistics = $this->photo1->statistics()->first();
		$this->assertNotNull($statistics);
		$this->assertEquals(1, $statistics->rating_sum);
		$this->assertEquals(1, $statistics->rating_count);
	}

	/**
	 * Test that multiple users rating the same photo maintains statistics consistency.
	 * Sum and count should always match the actual ratings in the database.
	 */
	public function testMultipleUsersConcurrentRatingsMaintainStatisticsConsistency(): void
	{
		// Three users rate the same photo
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		$this->actingAs($this->userMayUpload2)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		// Verify statistics match actual ratings
		$expectedSum = DB::table('photo_ratings')
			->where('photo_id', $this->photo1->id)
			->sum('rating');

		$expectedCount = DB::table('photo_ratings')
			->where('photo_id', $this->photo1->id)
			->count();

		$statistics = $this->photo1->statistics()->first();
		$this->assertNotNull($statistics);
		$this->assertEquals($expectedSum, $statistics->rating_sum);
		$this->assertEquals($expectedCount, $statistics->rating_count);
		$this->assertEquals(12, $statistics->rating_sum); // 5 + 4 + 3
		$this->assertEquals(3, $statistics->rating_count);
	}

	/**
	 * Test that adding and removing ratings maintains statistics consistency.
	 */
	public function testAddAndRemoveRatingsMaintainConsistency(): void
	{
		// User 1 rates
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// User 2 rates
		$this->actingAs($this->userMayUpload2)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		// User 1 removes rating
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		// Verify statistics: only user2's rating remains
		$statistics = $this->photo1->statistics()->first();
		$this->assertNotNull($statistics);
		$this->assertEquals(4, $statistics->rating_sum);
		$this->assertEquals(1, $statistics->rating_count);
	}
}
