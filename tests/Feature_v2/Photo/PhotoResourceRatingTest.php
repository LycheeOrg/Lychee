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

use App\Models\PhotoRating;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoResourceRatingTest extends BaseApiWithDataTest
{
	public function testPhotoResourceIncludesCurrentUserRating(): void
	{
		// Create a rating for the user
		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 4,
		]);

		// Set rating after creating the resource
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'rating' => [
				'rating_user' => 4,
			],
		]);
	}

	public function testPhotoResourceIncludesNullRatingForNonRatedPhoto(): void
	{
		// Photo has no ratings
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);

		$this->assertCreated($response);
		$response->assertJsonPath('rating.rating_user', 3);
	}

	public function testPhotoResourceIncludesRatingStatisticsWhenMetricsEnabled(): void
	{
		// Enable metrics and set access to owner (userMayUpload1 owns photo1)
		$this->setConfigValue('metrics_enabled', '1');
		$this->setConfigValue('metrics_access', 'owner');

		// Create multiple ratings
		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 5,
		]);

		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->admin->id,
			'rating' => 3,
		]);

		// Update statistics
		$statistics = $this->photo1->statistics()->firstOrCreate(
			['photo_id' => $this->photo1->id],
			[
				'album_id' => null,
				'visit_count' => 0,
				'download_count' => 0,
				'favourite_count' => 0,
				'shared_count' => 0,
				'rating_sum' => 8,
				'rating_count' => 2,
			]
		);
		$statistics->rating_sum = 8;
		$statistics->rating_count = 2;
		$statistics->save();

		// Fetch photo via setRating to get updated resource
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'rating' => [
				'rating_count' => 2,
				'rating_avg' => 4.0,
			],
		]);
	}

	public function testPhotoResourceUpdatesCurrentUserRatingAfterChange(): void
	{
		// Create initial rating
		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 2,
		]);

		// Update rating
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'rating' => [
				'rating_user' => 5,
			],
		]);
	}

	public function testPhotoResourceShowsNullRatingAfterRemoval(): void
	{
		// Create rating
		PhotoRating::create([
			'photo_id' => $this->photo1->id,
			'user_id' => $this->userMayUpload1->id,
			'rating' => 4,
		]);

		// Remove rating
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		$this->assertCreated($response);
		$response->assertJson([
			'rating' => [
				'rating_user' => 0,
			],
		]);
	}

	private function setConfigValue(string $key, string $value): void
	{
		\DB::table('configs')->updateOrInsert(
			['key' => $key],
			['value' => $value]
		);
	}
}
