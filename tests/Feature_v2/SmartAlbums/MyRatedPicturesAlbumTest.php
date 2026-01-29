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
 * Tests for MyRatedPicturesAlbum smart album (Feature 011).
 */
class MyRatedPicturesAlbumTest extends BaseApiWithDataTest
{
	/**
	 * S-011-01: Test authenticated user sees all photos they have rated.
	 */
	public function testAuthenticatedUserSeesRatedPhotos(): void
	{
		Configs::set('enable_my_rated_pictures', '1');

		// Rate photo1 and photo2 as admin
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 5,
		]);
		$this->actingAs($this->admin)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 3,
		]);

		// Get my_rated_pictures smart album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();

		// Should contain both rated photos
		$this->assertContains($this->photo1->id, $photoIds, 'Photo1 (5★) should be in MyRatedPicturesAlbum');
		$this->assertContains($this->photo2->id, $photoIds, 'Photo2 (3★) should be in MyRatedPicturesAlbum');

		// Should NOT contain unrated photo
		$this->assertNotContains($this->photo3->id, $photoIds, 'Photo3 (unrated) should NOT be in MyRatedPicturesAlbum');
	}

	/**
	 * S-011-03: Test guest user cannot see album (hidden from list).
	 */
	public function testGuestUserCannotSeeAlbum(): void
	{
		Configs::set('enable_my_rated_pictures', '1');

		// Get smart albums as guest
		$response = $this->getJson('Albums');
		$this->assertOk($response);

		$smartAlbumIds = collect($response->json('smart_albums'))->pluck('id')->all();

		// My Rated Pictures should not appear for guests
		$this->assertNotContains('my_rated_pictures', $smartAlbumIds, 'MyRatedPicturesAlbum should NOT appear for guest users');
	}

	/**
	 * S-011-05: Test user with 0 ratings gets empty result.
	 */
	public function testUserWithZeroRatingsGetsEmpty(): void
	{
		Configs::set('enable_my_rated_pictures', '1');

		// Get album for user who has not rated anything
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		$photos = $response->json('photos');
		$this->assertEmpty($photos, 'MyRatedPicturesAlbum should be empty when user has not rated any photos');
	}

	/**
	 * S-011-06: Test rating a photo makes it appear in album.
	 */
	public function testRatePhotoAppearsInAlbum(): void
	{
		Configs::set('enable_my_rated_pictures', '1');

		// Initially empty for userMayUpload1
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);
		$initialPhotos = collect($response->json('photos'))->pluck('id')->all();
		$this->assertEmpty($initialPhotos, 'Album should be initially empty');

		// Rate photo1
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo1->id,
			'rating' => 4,
		]);

		// Verify photo1 now appears
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		$updatedPhotos = collect($response->json('photos'))->pluck('id')->all();
		$this->assertContains($this->photo1->id, $updatedPhotos, 'Photo1 should appear after rating');
	}

	/**
	 * S-011-12: Test private photo respects permissions.
	 * Note: This test verifies that photos the user cannot access are excluded.
	 */
	public function testPrivatePhotoRespectsPermissions(): void
	{
		Configs::set('enable_my_rated_pictures', '1');

		// userMayUpload2 rates their own photo2 (which they own and can access)
		$this->actingAs($this->userMayUpload2)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 5,
		]);

		// userMayUpload2 should see photo2 in their rated album
		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertContains($this->photo2->id, $photoIds, 'User should see their own rated photo');

		// userMayUpload1 rates photo2 (owned by userMayUpload2, in album2 which userMayUpload1 does not have access to)
		// Note: photo2 is in album2 which is private to userMayUpload2
		$this->actingAs($this->userMayUpload1)->postJson('Photo::setRating', [
			'photo_id' => $this->photo2->id,
			'rating' => 4,
		]);

		// userMayUpload1 should NOT see photo2 in their rated album (no permission to view)
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		$photoIds = collect($response->json('photos'))->pluck('id')->all();
		$this->assertNotContains($this->photo2->id, $photoIds, 'User should NOT see private photos they rated but cannot access');
	}

	/**
	 * Test sorting: rating DESC, then created_at DESC.
	 */
	public function testSortingRatingThenCreatedAt(): void
	{
		Configs::set('enable_my_rated_pictures', '1');

		// Rate multiple photos with different ratings
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

		// Get album
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => 'my_rated_pictures']);
		$this->assertOk($response);

		$photos = $response->json('photos');

		// Verify order: photo2 (5★), photo3 (4★), photo1 (3★)
		$this->assertEquals($this->photo2->id, $photos[0]['id'], 'First photo should be highest rated (5★)');
		$this->assertEquals($this->photo3->id, $photos[1]['id'], 'Second photo should be second highest rated (4★)');
		$this->assertEquals($this->photo1->id, $photos[2]['id'], 'Third photo should be lowest rated (3★)');
	}
}
