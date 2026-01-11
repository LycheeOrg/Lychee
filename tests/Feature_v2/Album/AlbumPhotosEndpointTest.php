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

namespace Tests\Feature_v2\Album;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Tests for the /Album::photos endpoint which returns paginated photos.
 */
class AlbumPhotosEndpointTest extends BaseApiWithDataTest
{
	public function testGetAlbumPhotosFirstPage(): void
	{
		// Test with public album (album4 has photo4)
		$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson([
			'photos' => [
				[
					'id' => $this->photo4->id,
				],
			],
			'current_page' => 1,
			'per_page' => 100, // default config value
			'total' => 1,
			'last_page' => 1,
		]);
	}

	public function testGetAlbumPhotosWithPageParameter(): void
	{
		// Test page parameter explicitly set to 1
		$response = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album4->id,
			'page' => 1,
		]);
		$this->assertOk($response);
		$response->assertJson([
			'current_page' => 1,
		]);
	}

	public function testGetAlbumPhotosSecondPage(): void
	{
		// Test page 2 (will be empty for album4 which has only 1 photo)
		$response = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album4->id,
			'page' => 2,
		]);
		$this->assertOk($response);
		$response->assertJson([
			'photos' => [],
			'current_page' => 2,
			'total' => 1,
			'last_page' => 1,
		]);
	}

	public function testGetAlbumPhotosMultiplePhotos(): void
	{
		// Test with album1 which has 2 photos (photo1, photo1b)
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::photos', [
			'album_id' => $this->album1->id,
		]);
		$this->assertOk($response);
		$response->assertJson([
			'total' => 2,
			'current_page' => 1,
			'last_page' => 1,
		]);
		// Verify both photos are present
		$photos = $response->json('photos');
		$this->assertCount(2, $photos);
		$photoIds = array_column($photos, 'id');
		$this->assertContains($this->photo1->id, $photoIds);
		$this->assertContains($this->photo1b->id, $photoIds);
	}

	public function testGetAlbumPhotosUnauthorized(): void
	{
		// Test with private album as anonymous user
		$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertUnauthorized($response);
	}

	public function testGetAlbumPhotosForbidden(): void
	{
		// Test with private album as different user
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::photos', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);
	}

	public function testGetAlbumPhotosInvalidPage(): void
	{
		// Test with invalid page parameter (zero)
		$response = $this->getJsonWithData('Album::photos', [
			'album_id' => $this->album4->id,
			'page' => 0,
		]);
		$this->assertUnprocessable($response);
	}

	public function testGetAlbumPhotosMissingAlbumId(): void
	{
		// Test without album_id parameter
		$response = $this->getJson('Album::photos');
		$this->assertUnprocessable($response);
		$response->assertJson([
			'message' => 'The album id field is required.',
		]);
	}
}
