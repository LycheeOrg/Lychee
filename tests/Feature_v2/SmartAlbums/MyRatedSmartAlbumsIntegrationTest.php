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
 * Integration tests for My Rated smart albums (Feature 011).
 * Tests end-to-end scenarios, config enable/disable, SE license, and photo visibility.
 */
class MyRatedSmartAlbumsIntegrationTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * S-011-10: Test config enable/disable for My Rated Pictures.
	 */
	public function testConfigEnableDisableMyRatedPictures(): void
	{
		// Enable the album
		Configs::set('enable_my_rated_pictures', '1');

		// Rate a photo
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// Album should appear in smart albums list
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertContains('my_rated_pictures', $smartAlbumIds, 'Album should appear when enabled');

		// Album should be accessible
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		// Disable the album
		Configs::set('enable_my_rated_pictures', '0');

		// Album should NOT appear in smart albums list
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertNotContains('my_rated_pictures', $smartAlbumIds, 'Album should NOT appear when disabled');
	}

	/**
	 * S-011-10: Test config enable/disable for My Best Pictures.
	 */
	public function testConfigEnableDisableMyBestPictures(): void
	{
		// Enable the album
		Configs::set('enable_my_best_pictures', '1');

		// Rate a photo
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// Album should appear in smart albums list (with SE license)
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertContains('my_best_pictures', $smartAlbumIds, 'Album should appear when enabled with SE license');

		// Album should be accessible
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);

		// Disable the album
		Configs::set('enable_my_best_pictures', '0');

		// Album should NOT appear in smart albums list
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertNotContains('my_best_pictures', $smartAlbumIds, 'Album should NOT appear when disabled');
	}

	/**
	 * S-011-11: Test SE license requirement for My Best Pictures.
	 */
	public function testSeLicenseRequirementMyBestPictures(): void
	{
		Configs::set('enable_my_best_pictures', '1');

		// With SE license, album should appear
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertContains('my_best_pictures', $smartAlbumIds, 'Album should appear with SE license');

		// Remove SE license
		$this->resetSe();

		// Album should NOT appear without SE license
		$response = $this->actingAs($this->admin)->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();
		$this->assertNotContains('my_best_pictures', $smartAlbumIds, 'Album should NOT appear without SE license');
	}

	/**
	 * S-011-12: Test photo visibility filtering.
	 * Rated photos that the user cannot access should not appear in albums.
	 */
	public function testPhotoVisibilityFiltering(): void
	{
		Configs::set('enable_my_rated_pictures', '1');
		Configs::set('enable_my_best_pictures', '1');

		// userMayUpload1 rates their own photo1 (accessible)
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// userMayUpload1 rates photo3 (owned by userNoUpload, in private album3)
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 4,
		]);

		// Get My Rated Pictures for userMayUpload1
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should see photo1 (owned by user)
		$this->assertContains($this->photo1->id, $photoIds, 'User should see their own rated photo');

		// Should NOT see photo3 (no permission to view)
		$this->assertNotContains($this->photo3->id, $photoIds, 'User should NOT see private photos they rated but cannot access');

		// Get My Best Pictures for userMayUpload1
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should see photo1 (owned by user)
		$this->assertContains($this->photo1->id, $photoIds, 'User should see their own rated photo in Best Pictures');

		// Should NOT see photo3 (no permission to view)
		$this->assertNotContains($this->photo3->id, $photoIds, 'User should NOT see private photos in Best Pictures');
	}

	/**
	 * Test rating update interaction with both albums.
	 */
	public function testRatingUpdateInteraction(): void
	{
		Configs::set('enable_my_rated_pictures', '1');
		Configs::set('enable_my_best_pictures', '1');
		Configs::set('my_best_pictures_count', '2');

		// Rate 3 photos
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 3,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo3->id,
			'rating' => 4,
		]);

		// Get My Rated Pictures - should contain all 3
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);
		$ratedPhotoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertCount(3, $ratedPhotoIds, 'My Rated Pictures should contain all 3 rated photos');

		// Get My Best Pictures - should contain top 2: photo2 (5★), photo3 (4★)
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);
		$bestPhotoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertCount(2, $bestPhotoIds, 'My Best Pictures should contain top 2 photos');
		$this->assertContains($this->photo2->id, $bestPhotoIds, 'Photo2 (5★) should be in Best Pictures');
		$this->assertContains($this->photo3->id, $bestPhotoIds, 'Photo3 (4★) should be in Best Pictures');
		$this->assertNotContains($this->photo1->id, $bestPhotoIds, 'Photo1 (3★) should NOT be in Best Pictures');

		// Update photo1 rating to 5★
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// Get My Best Pictures again - should now contain only photo1 and photo2 (both 5★)
		// Cutoff is at 2nd photo (5★), so only 5★ photos are included
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_best_pictures']);
		$this->assertOk($response);
		$bestPhotoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertCount(2, $bestPhotoIds, 'My Best Pictures should contain 2 photos (2×5★)');
		$this->assertContains($this->photo1->id, $bestPhotoIds, 'Photo1 (5★) should now be in Best Pictures');
		$this->assertContains($this->photo2->id, $bestPhotoIds, 'Photo2 (5★) should be in Best Pictures');
		$this->assertNotContains($this->photo3->id, $bestPhotoIds, 'Photo3 (4★) should NOT be in Best Pictures (below cutoff)');

		// Remove rating from photo1
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 0,
		]);

		// Get My Rated Pictures - should only contain photo2 and photo3 now
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);
		$ratedPhotoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertCount(2, $ratedPhotoIds, 'My Rated Pictures should contain 2 photos after unrating');
		$this->assertNotContains($this->photo1->id, $ratedPhotoIds, 'Photo1 should be removed after unrating');
	}

	/**
	 * Test that albums show only the current user's ratings.
	 * Different users should see different photos based on their own ratings.
	 */
	public function testAlbumsShowOnlyCurrentUserRatings(): void
	{
		Configs::set('enable_my_rated_pictures', '1');

		// Admin rates photo1 (which admin can see)
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);

		// userMayUpload1 rates their own photo1 (which they can see)
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		// Admin should see photo1 in their My Rated Pictures (they rated it)
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);
		$adminPhotoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertContains($this->photo1->id, $adminPhotoIds, 'Admin should see photo1');

		// userMayUpload1 should also see photo1 in their My Rated Pictures (they own and rated it)
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);
		$user1PhotoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertContains($this->photo1->id, $user1PhotoIds, 'userMayUpload1 should see their own photo1');

		// userMayUpload2 should NOT see photo1 in their album (they didn't rate it)
		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);
		$user2PhotoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertNotContains($this->photo1->id, $user2PhotoIds, 'userMayUpload2 should NOT see photo1 (did not rate it)');
	}
}
