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
use Tests\Traits\PostPhoto;

class PhotoAddTest extends BaseApiV2Test
{
	use PostPhoto;

	public function testAddPhotoUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo', [
			'album_id' => null,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::PHOTO_NIGHT_TITLE . '.jpg',
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo', [
			'album_id' => null,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::PHOTO_NIGHT_TITLE . '.jpg',
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		]);
		$this->assertForbidden($response);
	}

	public function testAddPhotoAuthorizedOwner(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->postJson('Photo', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo', [
			'album_id' => $this->album3->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::PHOTO_NIGHT_TITLE . '.jpg',
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->admin)->postJson('Photo', [
			'album_id' => null,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::PHOTO_NIGHT_TITLE . '.jpg',
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		]);
		$this->assertCreated($response);
		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}
}