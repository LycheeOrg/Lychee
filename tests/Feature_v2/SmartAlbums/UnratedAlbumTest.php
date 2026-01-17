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
 * Tests for UnratedAlbum smart album (Feature 009, T-009-15).
 *
 * Verifies that UnratedAlbum contains only photos with rating_avg IS NULL (S-009-04).
 */
class UnratedAlbumTest extends BaseApiWithDataTest
{
	/**
	 * T-009-15: Test that UnratedAlbum contains only photos with no ratings.
	 */
	public function testUnratedAlbumContainsOnlyUnratedPhotos(): void
	{
		// Ensure unrated smart album is enabled
		Configs::set('enable_unrated', '1');

		// Verify photo1 starts unrated
		$this->photo1->refresh();
		$this->assertNull($this->photo1->rating_avg);

		// Rate photo2 so it won't appear in unrated
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 3,
		]);

		// Verify photo2 now has a rating
		$this->photo2->refresh();
		$this->assertEquals('3.0000', $this->photo2->rating_avg);

		// Get unrated smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'unrated']);
		$this->assertOk($response);

		$photos = $response->json('resource.photos');

		// Verify photo1 (unrated) is present
		$photoIds = collect($photos)->pluck('id')->all();
		$this->assertContains($this->photo1->id, $photoIds, 'Unrated photo should be in UnratedAlbum');

		// Verify photo2 (rated) is NOT present
		$this->assertNotContains($this->photo2->id, $photoIds, 'Rated photo should NOT be in UnratedAlbum');
	}

	/**
	 * Test that photos become included/excluded when rating changes.
	 */
	public function testUnratedAlbumUpdatesWhenRatingChanges(): void
	{
		Configs::set('enable_unrated', '1');

		// Initially photo1 is unrated
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'unrated']);
		$this->assertOk($response);

		$initialPhotos = collect($response->json('resource.photos'))->pluck('id')->all();
		$this->assertContains($this->photo1->id, $initialPhotos, 'Photo1 should be in UnratedAlbum initially');

		// Rate photo1
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		// Verify photo1 is no longer in unrated album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'unrated']);
		$this->assertOk($response);

		$updatedPhotos = collect($response->json('resource.photos'))->pluck('id')->all();
		$this->assertNotContains($this->photo1->id, $updatedPhotos, 'Photo1 should NOT be in UnratedAlbum after rating');

		// Remove rating
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		// Verify photo1 is back in unrated album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => 'unrated']);
		$this->assertOk($response);

		$finalPhotos = collect($response->json('resource.photos'))->pluck('id')->all();
		$this->assertContains($this->photo1->id, $finalPhotos, 'Photo1 should be back in UnratedAlbum after removing rating');
	}

	/**
	 * Test that album can be disabled via config.
	 */
	public function testUnratedAlbumCanBeDisabled(): void
	{
		// Disable unrated smart album
		Configs::set('enable_unrated', '0');

		// Verify album list does not contain unrated
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbums = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertNotContains('unrated', $smartAlbums, 'Unrated album should not appear when disabled');

		// Re-enable
		Configs::set('enable_unrated', '1');

		// Verify album list now contains unrated
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbums = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertContains('unrated', $smartAlbums, 'Unrated album should appear when enabled');
	}
}
