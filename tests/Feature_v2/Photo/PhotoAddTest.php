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

class PhotoAddTest extends BaseApiWithDataTest
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

		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [
				'is_base_album' => false,
				'is_model_album' => false,
				'is_accessible' => true,
				'is_password_protected' => false,
				'is_search_accessible' => false,
			],
			'resource' => [
				'id' => 'unsorted',
				'title' => 'Unsorted',
				'photos' => [
					[
						'title' => TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
					],
				],
			],
		]);
		$response = $this->deleteJson('Photo', ['photo_ids' => [$response->json('resource.photos.0.id')]]);
		$this->assertNoContent($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');
	}

	public function testDuplicate(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);

		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$id1 = $response->json('resource.photos.0.id');
		$id2 = $response->json('resource.photos.1.id');

		self::assertNotEquals($id1, $id2);

		$response = $this->deleteJson('Photo', ['photo_ids' => [$id1, $id2]]);
		$this->assertNoContent($response);

		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}

	public function testNotify(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album3->id);
		$this->assertCreated($response);
		$this->catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];
	}

	public function testAppleLivePhoto1(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_TRAIN_IMAGE);
		$this->assertCreated($response);
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_TRAIN_VIDEO);
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$photo = $response->json('resource.photos.0');

		self::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo['live_photo_content_id']);

		self::assertStringEndsWith('.mov', $photo['live_photo_url']);
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_DIRNAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_DIRNAME));
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_FILENAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_FILENAME));
	}

	public function testAppleLivePhoto2(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_TRAIN_VIDEO);
		$this->assertCreated($response);
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_TRAIN_IMAGE);
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$photo = $response->json('resource.photos.0');

		self::assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo['live_photo_content_id']);

		self::assertStringEndsWith('.mov', $photo['live_photo_url']);
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_DIRNAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_DIRNAME));
		self::assertEquals(pathinfo($photo['live_photo_url'], PATHINFO_FILENAME), pathinfo($photo['size_variants']['original']['url'], PATHINFO_FILENAME));
	}

	public function testGoogleMotionPicture(): void
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_GMP_IMAGE);
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$id = $response->json('resource.photos.0.id');
		$response = $this->deleteJson('Photo', ['photo_ids' => [$id]]);
		$this->assertNoContent($response);
	}
}
