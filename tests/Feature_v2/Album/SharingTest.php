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

class SharingTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJsonWithData('Sharing');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Sharing', ['album_id' => $this->album1->id]);
		$this->assertUnauthorized($response);
	}

	public function testUserGet(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Sharing', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Sharing', [
			'perm_id' => $this->perm1->id,
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload2)->postJson('Sharing', [
			'user_ids' => [$this->userMayUpload1->id],
			'album_ids' => [$this->album2->id],
			'grants_edit' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);
	}
}