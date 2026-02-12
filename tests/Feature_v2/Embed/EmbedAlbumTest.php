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

namespace Tests\Feature_v2\Embed;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Facades\Hash;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Test the Embed API endpoint for external website integration.
 */
class EmbedAlbumTest extends BaseApiWithDataTest
{
	/**
	 * Test that public album can be embedded.
	 */
	public function testGetPublicAlbum(): void
	{
		// album4 is public (from test data)
		$response = $this->getJson('Embed/' . $this->album4->id);
		$this->assertOk($response);

		// Verify structure
		$response->assertJsonStructure([
			'album' => [
				'id',
				'title',
				'description',
				'photo_count',
				'copyright',
				'license',
			],
			'photos' => [
				'*' => [
					'id',
					'title',
					'description',
					'size_variants' => [
						'placeholder',
						'thumb',
						'thumb2x',
						'small',
						'small2x',
						'medium',
						'medium2x',
						'original' => [
							'width',
							'height',
						],
					],
					'exif' => [
						'make',
						'model',
						'lens',
						'iso',
						'aperture',
						'shutter',
						'focal',
						'taken_at',
					],
				],
			],
		]);

		// Verify album data
		$response->assertJson([
			'album' => [
				'id' => $this->album4->id,
				'title' => $this->album4->title,
			],
		]);
	}

	/**
	 * Test that private album cannot be embedded.
	 */
	public function testCannotGetPrivateAlbum(): void
	{
		// album1 is private (owned by user, not public)
		$response = $this->getJson('Embed/' . $this->album1->id);
		$this->assertUnauthorized($response);
	}

	/**
	 * Test that password-protected album cannot be embedded.
	 */
	public function testCannotGetPasswordProtectedAlbum(): void
	{
		// Make album public but password protected via access permission
		AccessPermission::factory()
			->public()
			->visible()
			->for_album($this->album1)
			->create([
				'password' => Hash::make('test123'),
			]);

		$response = $this->getJson('Embed/' . $this->album1->id);
		$this->assertUnauthorized($response);
	}

	/**
	 * Test that link-required album cannot be embedded.
	 */
	public function testCannotGetLinkRequiredAlbum(): void
	{
		// Make album public but link-required via access permission
		AccessPermission::factory()
			->public()
			->for_album($this->album1)
			->create([
				'is_link_required' => true,
			]);

		$response = $this->getJson('Embed/' . $this->album1->id);
		$this->assertUnauthorized($response);
	}

	/**
	 * Test that album with no photos can be embedded.
	 */
	public function testCanGetAlbumWithNoPhotos(): void
	{
		// Create a public album with no photos
		$emptyAlbum = Album::factory()
			->as_root()
			->owned_by($this->userMayUpload1)
			->create([
				'title' => 'Empty Album',
			]);

		AccessPermission::factory()
			->public()
			->visible()
			->for_album($emptyAlbum)
			->create();

		$response = $this->getJson('Embed/' . $emptyAlbum->id);
		$this->assertOk($response);

		$response->assertJson([
			'album' => [
				'id' => $emptyAlbum->id,
				'photo_count' => 0,
			],
			'photos' => [],
		]);

		$emptyAlbum->delete();
	}

	/**
	 * Test that non-existent album returns 404.
	 */
	public function testGetNonExistentAlbum(): void
	{
		$response = $this->getJson('Embed/non-existent-id');
		$this->assertNotFound($response);
	}

	/**
	 * Test that author filter returns only photos by the specified user.
	 */
	public function testAuthorFilterReturnsOnlyMatchingPhotos(): void
	{
		// album4 is public, owned by userLocked, contains photo4
		// Add a photo owned by a different user to the same album
		/** @disregard */
		$other_photo = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($this->album4)->create();

		// Without author filter: should return both photos
		$response = $this->getJson('Embed/' . $this->album4->id);
		$this->assertOk($response);
		$all_photo_ids = collect($response->json('photos'))->pluck('id')->toArray();
		$this->assertContains($this->photo4->id, $all_photo_ids);
		$this->assertContains($other_photo->id, $all_photo_ids);

		// With author filter: should return only the matching user's photo
		$response = $this->getJson('Embed/' . $this->album4->id . '?author=' . $this->userLocked->username);
		$this->assertOk($response);
		$filtered_photo_ids = collect($response->json('photos'))->pluck('id')->toArray();
		$this->assertContains($this->photo4->id, $filtered_photo_ids);
		$this->assertNotContains($other_photo->id, $filtered_photo_ids);

		// Verify photo_count reflects the filtered count
		$response->assertJson([
			'album' => [
				'photo_count' => count($filtered_photo_ids),
			],
		]);

		$other_photo->delete();
	}

	/**
	 * Test that comma-separated author filter returns photos from multiple users.
	 */
	public function testMultiAuthorFilterReturnsPhotosFromBothUsers(): void
	{
		// album4 is public, owned by userLocked, contains photo4
		// Add a photo owned by a different user to the same album
		/** @disregard */
		$other_photo = Photo::factory()->owned_by($this->userMayUpload1)->with_GPS_coordinates()->in($this->album4)->create();

		// Filter by both authors: should return photos from both users
		$response = $this->getJson('Embed/' . $this->album4->id . '?author=' . $this->userLocked->username . ',' . $this->userMayUpload1->username);
		$this->assertOk($response);
		$photo_ids = collect($response->json('photos'))->pluck('id')->toArray();
		$this->assertContains($this->photo4->id, $photo_ids, 'photo4 owned by userLocked should be included');
		$this->assertContains($other_photo->id, $photo_ids, 'other_photo owned by userMayUpload1 should be included');

		// Filter by only one author: should exclude the other
		$response = $this->getJson('Embed/' . $this->album4->id . '?author=' . $this->userMayUpload1->username);
		$this->assertOk($response);
		$filtered_ids = collect($response->json('photos'))->pluck('id')->toArray();
		$this->assertContains($other_photo->id, $filtered_ids);
		$this->assertNotContains($this->photo4->id, $filtered_ids);

		$other_photo->delete();
	}

	/**
	 * Test that author filter with non-existent username returns empty photos.
	 */
	public function testAuthorFilterNonExistentUserReturnsEmpty(): void
	{
		$response = $this->getJson('Embed/' . $this->album4->id . '?author=nonexistentuser99');
		$this->assertOk($response);

		$response->assertJson([
			'album' => [
				'photo_count' => 0,
			],
			'photos' => [],
		]);
	}
}
