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

use App\Models\AccessPermission;
use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumMoveTest extends BaseApiV2Test
{
	public function testMoveAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::move', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertForbidden($response);
	}

	public function testMoveAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->subAlbum1->id);
	}

	public function testMoveAlbumAuthorizedUser(): void
	{
		AccessPermission::factory()
		->for_user($this->userMayUpload2)
		->for_album($this->subAlbum1)
		->visible()
		->grants_edit()
		->grants_delete()
		->grants_upload()
		->grants_download()
		->grants_full_photo()
		->create();

		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::move', [
			'album_id' => null,
			'album_ids' => [$this->subAlbum1->id],
		]);
		$this->assertNoContent($response);
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->subAlbum1->id);
	}
}