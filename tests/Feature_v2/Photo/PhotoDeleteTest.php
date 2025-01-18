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

namespace Tests\Feature_v2\Photo;

use Tests\Feature_v2\Base\BaseApiV2Test;

class PhotoDeleteTest extends BaseApiV2Test
{
	public function testDeletePhotoUnauthorizedForbidden(): void
	{
		$response = $this->deleteJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
			'album_id' => $this->album2->id,
		]);
		$this->assertForbidden($response);
	}

	public function testDeletePhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userLocked)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');
	}
}