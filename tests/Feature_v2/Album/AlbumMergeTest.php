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

class AlbumMergeTest extends BaseApiV2Test
{
	public function testMergeAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::merge', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::merge', [
			'album_id' => $this->album1->id,
			'album_ids' => [$this->album2->id],
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Album::merge', [
			'album_id' => $this->album1->id,
			'album_ids' => [$this->album2->id],
		]);
		$this->assertForbidden($response);
	}

	public function testMergeAlbumAuthorizedUser(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::merge', [
			'album_id' => $this->album1->id, // has edit rights
			'album_ids' => [$this->album2->id], // own
		]);
		$this->assertNoContent($response);
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->album1->id);
		$response->assertDontSee($this->album2->id);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertSee($this->subAlbum1->id);
		$response->assertSee($this->subAlbum2->id);
	}
}