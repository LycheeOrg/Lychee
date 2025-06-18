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

class PhotoFromUrlTest extends BaseApiWithDataTest
{
	public function testAddPhotoUnauthorizedForbidden(): void
	{
		$response = $this->postJson(uri: 'Photo::fromUrl', data: []);
		$this->assertUnprocessable($response);

		$response = $this->postJson(uri: 'Photo::fromUrl', data: [
			'album_id' => $this->album1->id,
			'urls' => [],
		]);
		$this->assertUnprocessable($response);

		$response = $this->postJson(uri: 'Photo::fromUrl', data: [
			'album_id' => $this->album1->id,
			'urls' => ['example.com'],
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::fromUrl', data: [
			'album_id' => $this->album1->id,
			'urls' => ['example.com'],
		]);
		$this->assertForbidden($response);
	}

	public function testImportFromUrl(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Photo::fromUrl', data: [
			'album_id' => $this->album5->id,
			'urls' => [TestConstants::SAMPLE_DOWNLOAD_JPG],
		]);
		$this->assertOk($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);

		$response->assertJson([
			'resource' => [
				'photos' => [[
					'title' => 'mongolia',
					'type' => TestConstants::MIME_TYPE_IMG_JPEG,
					'size_variants' => [
						'original' => [
							'width' => 1280,
							'height' => 850,
							'filesize' => '196.60 KB',
						],
					],
				]],
			],
		]);
	}

	public function testImportFromUrlWithoutExtension(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Photo::fromUrl', data: [
			'album_id' => $this->album5->id,
			'urls' => [TestConstants::SAMPLE_DOWNLOAD_JPG_WITHOUT_EXTENSION],
		]);
		$this->assertOk($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);

		$response->assertJson([
			'resource' => [
				'photos' => [[
					'title' => 'mongolia',
					'type' => TestConstants::MIME_TYPE_IMG_JPEG,
					'size_variants' => [
						'original' => [
							'width' => 1280,
							'height' => 850,
							'filesize' => '196.60 KB',
						],
					],
				]],
			],
		]);
	}
}
