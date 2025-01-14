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

class AlbumRenameTest extends BaseApiV2Test
{
	public function testRenameAlbumUnauthorizedForbidden(): void
	{
		$response = $this->patchJson('Album::rename', []);
		$this->assertUnprocessable($response);

		$response = $this->patchJson('Album::rename', [
			'album_id' => $this->album1->id,
			'title' => 'new title',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->patchJson('Album::rename', [
			'album_id' => $this->album1->id,
			'title' => 'new title',
		]);
		$this->assertForbidden($response);
	}

	public function testRenameAlbumAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album::rename', [
			'album_id' => $this->album1->id,
			'title' => 'new title',
		]);
		$this->assertNoContent($response);
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'id' => $this->album1->id,
				'title' => 'new title',
			],
		]);
	}
}