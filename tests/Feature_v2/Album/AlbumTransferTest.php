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

class AlbumTransferTest extends BaseApiV2Test
{
	public function testTransferAlbumUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Album::transfer', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userMayUpload2->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userMayUpload2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testTransferAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album::transfer', [
			'album_id' => $this->album1->id,
			'user_id' => $this->userLocked->id,
		]);
		$this->assertNoContent($response);
		$response = $this->actingAs($this->userLocked)->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->album1->id);
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
	}
}