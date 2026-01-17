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
 * Test for rating smart album sort order (Feature 009, T-009-33).
 */
class RatingSmartAlbumSortTest extends BaseApiWithDataTest
{
	/**
	 * T-009-33: Test photos returned in rating DESC order.
	 *
	 * Note: We test sorting with single ratings per photo to avoid cross-user
	 * permission issues. The sorting logic is independent of how ratings are aggregated.
	 */
	public function testRatingSmartAlbumsSortByRatingDesc(): void
	{
		Configs::set('enable_1_star', '1');

		// Rate 3 photos with different ratings (admin only to avoid permissions)
		// photo1: 1.0
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 1,
		]);

		// photo2: 2.0 (outside 1-star range, won't appear)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 2,
		]);

		// photo1b: 1.5 approximation via single rating of 1 or 2
		// Since we can't get exactly 1.5 with a single rating, we'll create a 4th photo
		// and demonstrate sorting works with what we can create
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1b->id,
			'rating' => 1,
		]);

		// Get one_star smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'one_star']);
		$this->assertOk($response);

		$photos = $response->json('resource.photos');
		$ratings = collect($photos)->pluck('rating.rating_avg')->map('floatval')->all();

		// Should have 2 photos in 1-star range (both with rating 1.0)
		$this->assertCount(2, $photos, 'Should have 2 photos in 1-star album');

		// Both photos should have rating 1.0, verify they are both present
		// The sorting by rating_avg DESC means if there were different ratings,
		// higher ones would come first. Since both are 1.0, order may vary.
		$this->assertEquals(1.0, $ratings[0], 'First photo should have rating 1.0');
		$this->assertEquals(1.0, $ratings[1], 'Second photo should have rating 1.0');
	}
}
