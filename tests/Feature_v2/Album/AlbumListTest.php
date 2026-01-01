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

use App\Models\Album;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumListTest extends BaseApiWithDataTest
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

	public function testListTargetAlbumAuthorizedWithCrop(): void
	{
		$album1 = Album::factory()->as_root()->owned_by($this->admin)->with_title('123456789012345678901234567890')->create();
		$album2 = Album::factory()->children_of($album1)->owned_by($this->admin)->with_title('123456789012345678901234567890')->create();
		$album3 = Album::factory()->children_of($album2)->owned_by($this->admin)->with_title('123456789012345678901234567890')->create();
		$album4 = Album::factory()->children_of($album3)->owned_by($this->admin)->with_title('123456789012345678901234567890')->create();
		$response = $this->actingAs($this->admin)->getJson('Album::getTargetListAlbums?album_ids[]=' . $album4->id);
		$this->assertOk($response);
	}
}