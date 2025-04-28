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

use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiV2Test;

class PhotoAddTest extends BaseApiV2Test
{
	public function testAddPhotoUnauthorizedForbidden(): void
	{
		$response = $this->upload(uri: 'Photo', data: []);
		$this->assertUnprocessable($response);

		$response = $this->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->upload('Photo', TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertForbidden($response);
	}

	public function testAddPhotoAuthorizedOwner(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', data: []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album3->id);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);
		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}
}
