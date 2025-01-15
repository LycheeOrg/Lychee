<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumListTest extends BaseApiV2Test
{
	public function testListTargetAlbumUnauthorizedForbidden(): void
	{
		$response = $this->getJson('Album::getTargetListAlbums?album_ids[]=' . $this->album1->id);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userLocked)->getJson('Album::getTargetListAlbums?album_ids[]=' . $this->album1->id);
		$this->assertForbidden($response);
	}

	public function testListTargetAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Album::getTargetListAlbums?album_ids[]=' . $this->album1->id);
		$this->assertOk($response);
		$response->assertDontSee($this->subAlbum1->id);
	}
}