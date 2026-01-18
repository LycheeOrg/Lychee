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

class AlbumSetPinnedTest extends BaseApiWithDataTest
{
	public function testSetPinnedAlbumUnauthorizedForbidden(): void
	{
		$response = $this->patchJson('Album::setPinned', []);
		$this->assertUnprocessable($response);

		$response = $this->patchJson('Album::setPinned', [
			'album_id' => $this->album1->id,
			'is_pinned' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->patchJson('Album::setPinned', [
			'album_id' => $this->album1->id,
			'is_pinned' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testSetPinnedAlbumAuthorizedOwner(): void
	{
		// Test pinning an album
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::setPinned', [
			'album_id' => $this->album1->id,
			'is_pinned' => true,
		]);
		$this->assertNoContent($response);

		// Verify the album is pinned
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'id' => $this->album1->id,
				'is_pinned' => true,
			],
		]);

		// Test unpinning an album
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::setPinned', [
			'album_id' => $this->album1->id,
			'is_pinned' => false,
		]);
		$this->assertNoContent($response);

		// Verify the album is unpinned
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'id' => $this->album1->id,
				'is_pinned' => false,
			],
		]);
	}

	public function testSetPinnedAlbumAuthorizedAdmin(): void
	{
		// Test admin can pin any album
		$response = $this->actingAs($this->admin)->patchJson('Album::setPinned', [
			'album_id' => $this->album1->id,
			'is_pinned' => true,
		]);
		$this->assertNoContent($response);

		// Verify the album is pinned
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'id' => $this->album1->id,
				'is_pinned' => true,
			],
		]);
	}
}