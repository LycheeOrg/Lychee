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

namespace Tests\ImageProcessing\Photo;

use App\Exceptions\ZipInvalidException;
use function Safe\unlink;
use Symfony\Component\HttpFoundation\Response;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

class PhotoZipUploadTest extends BaseApiWithDataTest
{
	use RequireSE;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		// Force the queue to be synchronous for testing
		config(['queue.default' => 'sync']);
	}

	public function tearDown(): void
	{
		try {
			unlink(TestConstants::SAMPLE_TEST_ZIP);
		} catch (\Throwable) {
			// Nothing to do
		}

		$this->resetSe();
		parent::tearDown();
	}

	public function testZipExtract(): void
	{
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

		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$created_id = $response->json('resource.albums.0.id');
		$response->assertJsonPath('resource.albums.0.title', 'test_photos');

		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => $created_id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');
		$response->assertJsonPath('resource.photos.0.title', 'night');
		$response->assertJsonPath('resource.photos.1.title', 'sunset');
	}

	public function testZipExtractInFolders(): void
	{
		$zip = new \ZipArchive();
		if ($zip->open(TestConstants::SAMPLE_TEST_ZIP, \ZipArchive::CREATE) !== true) {
			$this->fail('Could not create zip file for testing.');
		}
		$zip->addFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, 'night/night.jpg');
		$zip->addFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, 'sunset/sunset.jpg');
		$zip->close();

		if (!file_exists(TestConstants::SAMPLE_TEST_ZIP)) {
			$this->fail('Did not create zip file for testing.');
		}

		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_TEST_ZIP, album_id: $this->album5->id);
		$this->assertCreated($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.albums');
		$albums = $response->json('resource.albums');
		$idx_night = array_search('night', array_column($albums, 'title'), true);
		$idx_sunset = array_search('sunset', array_column($albums, 'title'), true);
		$this->assertIsInt($idx_night);
		$this->assertIsInt($idx_sunset);
		$id_night = $albums[$idx_night]['id'];
		$id_sunset = $albums[$idx_sunset]['id'];

		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => $id_night]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');
		$response->assertJsonPath('resource.photos.0.title', 'night');

		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => $id_sunset]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');
		$response->assertJsonPath('resource.photos.0.title', 'sunset');
	}

	public function testBadZipExtract(): void
	{
		// $this->expectException(ZipInvalidException::class);

		// Create a bad zip file
		$zip = new \ZipArchive();
		if ($zip->open(TestConstants::SAMPLE_TEST_ZIP, \ZipArchive::CREATE) !== true) {
			$this->fail('Could not create zip file for testing.');
		}
		$zip->addFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, '../night.jpg');
		$zip->addFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE, '/sunset.jpg');
		$zip->close();

		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_TEST_ZIP, album_id: $this->album5->id);
		$this->assertStatus($response, Response::HTTP_I_AM_A_TEAPOT);
	}
}