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
 * Tests for the /Album::head endpoint which returns album metadata
 * without children/photos collections (used for pagination).
 */
class AlbumHeadEndpointTest extends BaseApiWithDataTest
{
	public function testGetAlbumHeadSuccess(): void
	{
		// Test with public album as anonymous user
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->album4->id,
				'title' => $this->album4->title,
				'has_albums' => true,
				'num_children' => 1, // subAlbum4
				'num_photos' => 1,   // photo4
			],
		]);

		// Verify NO children/photos arrays (key difference from /Album endpoint)
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetAlbumHeadAsOwner(): void
	{
		// Test with private album as owner
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->album1->id,
				'title' => $this->album1->title,
				'has_albums' => true,
				'num_children' => 1, // subAlbum1
				'num_photos' => 2,   // photo1, photo1b
			],
		]);

		// Verify NO children/photos arrays
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetAlbumHeadUnauthorized(): void
	{
		// Test with private album as anonymous user
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertUnauthorized($response);
	}

	public function testGetAlbumHeadForbidden(): void
	{
		// Test with private album as different user
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);
	}

	public function testGetAlbumHeadNotFound(): void
	{
		// Test with non-existent album ID (triggers validation error)
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::head', ['album_id' => 'nonexistent']);
		$this->assertUnprocessable($response);
	}

	public function testGetAlbumHeadMissingParameter(): void
	{
		// Test without album_id parameter
		$response = $this->getJson('Album::head');
		$this->assertUnprocessable($response);
		$response->assertJson([
			'message' => 'The album id field is required.',
		]);
	}

	public function testGetTagAlbumHeadSuccess(): void
	{
		// Test with tag album as owner
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->tagAlbum1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->tagAlbum1->id,
				'title' => $this->tagAlbum1->title,
			],
		]);

		// Verify NO children/photos arrays
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetTagAlbumHeadUnauthorized(): void
	{
		// Test with private tag album as anonymous user
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->tagAlbum1->id]);
		$this->assertUnauthorized($response);
	}

	public function testGetTagAlbumHeadForbidden(): void
	{
		// Test with private tag album as different user
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::head', ['album_id' => $this->tagAlbum1->id]);
		$this->assertForbidden($response);
	}

	public function testGetSmartAlbumUnsortedHeadSuccess(): void
	{
		// Test with unsorted smart album as owner
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => 'unsorted',
			],
		]);

		// Verify NO children/photos arrays
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetSmartAlbumHighlightedHeadSuccess(): void
	{
		// Test with highlighted smart album as owner
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => 'highlighted']);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => 'highlighted',
			],
		]);

		// Verify NO children/photos arrays
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetSmartAlbumRecentHeadSuccess(): void
	{
		// Test with recent smart album as owner
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => 'recent']);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => 'recent',
			],
		]);

		// Verify NO children/photos arrays
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetSmartAlbumOnThisDayHeadSuccess(): void
	{
		// Test with on_this_day smart album as owner
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => 'on_this_day']);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => 'on_this_day',
			],
		]);

		// Verify NO children/photos arrays
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetSmartAlbumUntaggedHeadSuccess(): void
	{
		// Test with untagged smart album as owner
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => 'untagged']);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => 'untagged',
			],
		]);

		// Verify NO children/photos arrays
		$response->assertJsonMissing(['albums', 'photos']);
	}

	public function testGetSmartAlbumHeadUnauthorized(): void
	{
		// Test with smart album as anonymous user (should fail)
		$response = $this->getJsonWithData('Album::head', ['album_id' => 'unsorted']);
		$this->assertUnauthorized($response);
	}
}
