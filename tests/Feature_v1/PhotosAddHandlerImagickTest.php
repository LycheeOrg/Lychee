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

namespace Tests\Feature_v1;

use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Traits\InteractsWithRaw;
use Tests\Traits\RequiresImageHandler;

/**
 * Runs the tests of {@link PhotosAddHandlerTestAbstract} with Imagick as image handler.
 */
class PhotosAddHandlerImagickTest extends BasePhotosAddHandler
{
	use InteractsWithRaw;
	use RequiresImageHandler;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresImagick();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresImageHandler();
		parent::tearDown();
	}

	/**
	 * Tests uploading of an accepted TIFF.
	 *
	 * As Imagick supports TIFFs, we also expect generated thumbnail.
	 *
	 * @return void
	 */
	public function testAcceptedRawUpload(): void
	{
		$acceptedRawFormats = static::getAcceptedRawFormats();
		try {
			static::setAcceptedRawFormats('.tif');

			/** @var \App\Models\Photo $photo */
			$photo = static::convertJsonToObject($this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TIFF)
			));

			self::assertStringEndsWith('.tif', $photo->size_variants->original->url);
			self::assertEquals(TestConstants::MIME_TYPE_IMG_TIFF, $photo->type);
			self::assertNotNull($photo->size_variants->thumb);
		} finally {
			static::setAcceptedRawFormats($acceptedRawFormats);
		}
	}
}
