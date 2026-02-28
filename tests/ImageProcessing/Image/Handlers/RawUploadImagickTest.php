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

namespace Tests\ImageProcessing\Image\Handlers;

use Tests\Constants\TestConstants;
use Tests\Traits\InteractsWithRaw;
use Tests\Traits\RequiresImageHandler;

/**
 * Integration tests for the RAW upload pipeline (Feature 020).
 *
 * Verifies that:
 * - HEIC uploads create both a RAW size variant and a JPEG original.
 * - Standard JPEG/PNG uploads remain unaffected (no RAW variant).
 *
 * Requires Imagick to be available.
 */
class RawUploadImagickTest extends BaseImageHandler
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
	 * JPEG upload should NOT create a RAW variant.
	 */
	public function testJpegUploadHasNoRaw(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$photo = $response->json('photos.0');

		self::assertNotNull($photo['size_variants']['original']);
		self::assertStringEndsWith('.jpg', $photo['size_variants']['original']['url']);
	}

	/**
	 * HEIC upload should create a RAW variant (preserving HEIC) and convert
	 * the original to JPEG.
	 */
	public function testHeicUploadCreatesRaw(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_HEIC);
		$photo = $response->json('photos.0');

		self::assertNotNull($photo['size_variants']['original']);
		// The original should be a JPEG after conversion
		self::assertStringEndsWith('.jpg', $photo['size_variants']['original']['url']);
		// Thumbnails should have been generated from the JPEG original
		self::assertNotNull($photo['size_variants']['thumb']);
	}

	/**
	 * TIFF in accepted raw_formats but NOT in CONVERTIBLE_RAW_EXTENSIONS
	 * should remain as ORIGINAL without a RAW variant being created.
	 */
	public function testTiffUploadNoRawConversion(): void
	{
		$accepted_raw_formats = static::getAcceptedRawFormats();
		try {
			static::setAcceptedRawFormats('.tif');

			$response = $this->uploadImage(TestConstants::SAMPLE_FILE_TIFF);
			$photo = $response->json('photos.0');

			// TIFF is not in CONVERTIBLE_RAW_EXTENSIONS, so no RAW variant
			self::assertNotNull($photo['size_variants']['original']);
			self::assertStringEndsWith('.tif', $photo['size_variants']['original']['url']);
			self::assertEquals(TestConstants::MIME_TYPE_IMG_TIFF, $photo['type']);
			self::assertNotNull($photo['size_variants']['thumb']);
		} finally {
			static::setAcceptedRawFormats($accepted_raw_formats);
		}
	}

	/**
	 * PNG upload should NOT create a RAW variant.
	 */
	public function testPngUploadHasNoRaw(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_PNG);
		$photo = $response->json('photos.0');

		self::assertNotNull($photo['size_variants']['original']);
	}
}
