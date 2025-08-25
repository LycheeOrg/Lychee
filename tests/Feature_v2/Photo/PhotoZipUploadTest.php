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
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

use function Safe\unlink;

class PhotoZipUploadTest extends BaseApiWithDataTest
{
	use RequireSE;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSE();

	}

	public function tearDown(): void
	{
		try {
			unlink(TestConstants::SAMPLE_TEST_ZIP);
		} catch (\Throwable) {
			// Nothing to do
		}

		$this->resetSE();
		parent::tearDown();
	}

	public function testZipExtract(): void
	{
		// $this->catchFailureSilence = [];
		// Create a zip file with two images

		$zip = new \ZipArchive();
		if ($zip->open(TestConstants::SAMPLE_TEST_ZIP, \ZipArchive::CREATE) !== true) {
			$this->fail('Could not create zip file for testing.');
		}
		$zip->addFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, 'night.jpg');
		$zip->addFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, 'sunset.jpg');
		$zip->close();

		if (!file_exists(TestConstants::SAMPLE_TEST_ZIP)) {
			$this->fail('Did not create zip file for testing.');
		}

		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_TEST_ZIP, album_id: $this->album5->id);
		$this->assertCreated($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Album::get', ['albumId' => $this->album5->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'data.photos');
		$response->assertJsonFragment(['title' => 'night']);
		$response->assertJsonFragment(['title' => 'sunset']);
	}

}