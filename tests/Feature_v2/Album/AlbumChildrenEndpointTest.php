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
 * Tests for the /Album::albums endpoint which returns paginated child albums.
 */
class AlbumChildrenEndpointTest extends BaseApiWithDataTest
{
	public function testGetAlbumChildrenFirstPage(): void
	{
		// Test with public album (album4 has subAlbum4)
		$response = $this->getJsonWithData('Album::albums', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response->assertJson([
			'data' => [
				[
					'id' => $this->subAlbum4->id,
					'title' => $this->subAlbum4->title,
				],
			],
			'current_page' => 1,
			'per_page' => 30, // default config value
			'total' => 1,
			'last_page' => 1,
		]);
	}

	public function testGetAlbumChildrenWithPageParameter(): void
	{
		// Test page parameter explicitly set to 1
		$response = $this->getJsonWithData('Album::albums', [
			'album_id' => $this->album4->id,
			'page' => 1,
		]);
		$this->assertOk($response);
		$response->assertJson([
			'current_page' => 1,
		]);
	}

	public function testGetAlbumChildrenSecondPage(): void
	{
		// Test page 2 (will be empty for album4 which has only 1 child)
		$response = $this->getJsonWithData('Album::albums', [
			'album_id' => $this->album4->id,
			'page' => 2,
		]);
		$this->assertOk($response);
		$response->assertJson([
			'data' => [],
			'current_page' => 2,
			'total' => 1,
			'last_page' => 1,
		]);
	}

	public function testGetAlbumChildrenUnauthorized(): void
	{
		// Test with private album as anonymous user
		$response = $this->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertUnauthorized($response);
	}

	public function testGetAlbumChildrenForbidden(): void
	{
		// Test with private album as different user
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album::albums', ['album_id' => $this->album1->id]);
		$this->assertForbidden($response);
	}

	public function testGetAlbumChildrenInvalidPage(): void
	{
		// Test with invalid page parameter (negative)
		$response = $this->getJsonWithData('Album::albums', [
			'album_id' => $this->album4->id,
			'page' => -1,
		]);
		$this->assertUnprocessable($response);
	}

	public function testGetAlbumChildrenMissingAlbumId(): void
	{
		// Test without album_id parameter
		$response = $this->getJson('Album::albums');
		$this->assertUnprocessable($response);
		$response->assertJson([
			'message' => 'The album id field is required.',
		]);
	}
}
