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

namespace Tests\Feature_v2\Commands;

use App\Models\Photo;
use App\Models\Statistics;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Test for lychee:sync-rating-avg artisan command (Feature 009, T-009-36).
 */
class SyncRatingAvgCommandTest extends BaseApiWithDataTest
{
	/**
	 * Test that command syncs rating_avg correctly.
	 */
	public function testCommandSyncsRatingAvg(): void
	{
		// Create a photo and manually set outdated rating_avg
		$photo = Photo::factory()->owned_by($this->admin)->create();

		// Create statistics with ratings
		$ratingSumValue = 10;
		$ratingCountValue = 2;

		$stats = Statistics::firstOrCreate(['photo_id' => $photo->id]);
		$stats->rating_sum = $ratingSumValue;
		$stats->rating_count = $ratingCountValue;
		$stats->save();

		// Calculate expected rating_avg
		$expectedRating = number_format($ratingSumValue / $ratingCountValue, 4, '.', '');

		// Manually set photo rating_avg to something wrong to simulate outdated data
		$photo->rating_avg = null;
		$photo->save();

		// Run the command
		$this->artisan('lychee:sync-rating-avg', ['--force' => true])
			->assertSuccessful();

		// Verify rating_avg was updated
		$photo->refresh();
		$this->assertEquals($expectedRating, $photo->rating_avg, 'rating_avg should be synced from statistics');
	}

	/**
	 * Test that command handles dry-run mode.
	 */
	public function testCommandDryRun(): void
	{
		// Create a photo with outdated rating_avg
		$photo = Photo::factory()->owned_by($this->admin)->create();

		$stats = Statistics::firstOrCreate(['photo_id' => $photo->id]);
		$stats->rating_sum = 15;
		$stats->rating_count = 3;
		$stats->save();

		// Set wrong rating_avg
		$photo->rating_avg = null;
		$photo->save();

		// Run in dry-run mode
		$this->artisan('lychee:sync-rating-avg', ['--dry-run' => true])
			->assertSuccessful();

		// Verify rating_avg was NOT updated
		$photo->refresh();
		$this->assertNull($photo->rating_avg, 'rating_avg should not be updated in dry-run mode');
	}

	/**
	 * Test that command handles photos with no ratings.
	 */
	public function testCommandSkipsPhotosWithoutRatings(): void
	{
		// Create a photo with no ratings
		$photo = Photo::factory()->owned_by($this->admin)->create();

		$stats = Statistics::firstOrCreate(['photo_id' => $photo->id]);
		$stats->rating_sum = 0;
		$stats->rating_count = 0;
		$stats->save();

		// Set rating_avg to null initially
		$photo->rating_avg = null;
		$photo->save();

		// Run the command
		$this->artisan('lychee:sync-rating-avg', ['--force' => true])
			->assertSuccessful();

		// Verify rating_avg is still null
		$photo->refresh();
		$this->assertNull($photo->rating_avg, 'rating_avg should remain null for unrated photos');
	}

	/**
	 * Test that command reports when all photos are synchronized.
	 */
	public function testCommandReportsWhenSynchronized(): void
	{
		// Create a photo with already correct rating_avg
		$photo = Photo::factory()->owned_by($this->admin)->create();

		$stats = Statistics::firstOrCreate(['photo_id' => $photo->id]);
		$stats->rating_sum = 9;
		$stats->rating_count = 2;
		$stats->save();

		// Set correct rating_avg
		$photo->rating_avg = 4.5000;
		$photo->save();

		// Run the command
		$this->artisan('lychee:sync-rating-avg', ['--force' => true])
			->expectsOutput('✓ All photos are already synchronized.')
			->assertSuccessful();
	}
}
