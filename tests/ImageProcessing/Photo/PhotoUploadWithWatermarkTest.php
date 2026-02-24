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

namespace Tests\ImageProcessing\Photo;

use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoUploadWithWatermarkTest extends BaseApiWithDataTest
{
	public function testUploadWithApplyWatermarkTrue(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'apply_watermark' => true,
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);
	}

	public function testUploadWithApplyWatermarkFalse(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'apply_watermark' => false,
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);
	}

	public function testUploadWithoutApplyWatermarkParameter(): void
	{
		// Test that the parameter is optional - absence should not cause error
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertCreated($response);
	}

	public function testUploadWithInvalidApplyWatermarkValue(): void
	{
		$data = [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			'file_last_modified_time' => 1678824303000,
			'file_name' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'apply_watermark' => 'invalid',
		];

		$response = $this->actingAs($this->admin)->upload('Photo', data: $data);
		$this->assertUnprocessable($response);
	}
}
