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

use function Safe\date;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use Tests\Constants\TestConstants;
use Tests\Traits\InteractsWithRaw;
use Tests\Traits\RequiresImageHandler;

/**
 * Runs the tests of {@link PhotosAddHandlerTestAbstract} with Imagick as image handler.
 */
class PhotosAddHandlerImagickTest extends BaseImageHandler
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
	 * Tests that uploading a PDF generates a thumbnail from the first page.
	 *
	 * PDF support is always enabled; Imagick delegates rendering to Ghostscript.
	 *
	 * @return void
	 */
	public function testPdfUploadCreatesThumbnail(): void
	{
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_PDF);
		$photo = $response->json('photos.0');

		self::assertEquals(TestConstants::MIME_TYPE_APP_PDF, $photo['type']);
		self::assertNotNull($photo['size_variants']['thumb']);
		self::assertEquals(200, $photo['size_variants']['thumb']['width']);
		self::assertEquals(200, $photo['size_variants']['thumb']['height']);
	}

	/**
	 * Tests that a PDF declaring an oversized `/MediaBox` on its first page
	 * is rejected before ever being handed to Ghostscript.
	 *
	 * A crafted PDF can declare an enormous page size while remaining tiny on
	 * disk. Without an upfront check, Ghostscript would spend a large amount
	 * of CPU time attempting to rasterize such a page before ultimately
	 * failing to produce any output.
	 *
	 * As with other thumbnail-generation failures (e.g. a broken Google
	 * Motion Photo, see {@see testBrokenGoogleMotionPhotoUpload}), the import
	 * follows a best-effort approach: the upload still succeeds and the
	 * original file is kept, but no size variants are generated and the
	 * error is logged. What matters here is that this happens near-instantly
	 * rather than after minutes of wasted CPU time.
	 *
	 * @return void
	 */
	public function testOversizedPdfMediaBoxIsRejected(): void
	{
		file_put_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log'), '');

		$started_at = microtime(true);
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_PDF_OVERSIZED_MEDIABOX);
		$elapsed = microtime(true) - $started_at;

		$photo = $response->json('photos.0');

		self::assertEquals(TestConstants::MIME_TYPE_APP_PDF, $photo['type']);
		self::assertNull( $photo['size_variants']['thumb']);
		self::assertNotEmpty(file_get_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log')));
		self::assertLessThan(10, $elapsed);
	}

	/**
	 * Tests that a PDF whose `/MediaBox` is implausibly large relative to its
	 * file size is rejected, even though its dimensions individually stay
	 * under the absolute cap tested by {@see testOversizedPdfMediaBoxIsRejected}.
	 *
	 * A ~350-byte file declaring a 20000x20000pt page has no plausible
	 * legitimate content behind it (a real single-page PDF, even a lean
	 * vector-only one, sits in the tens to low hundreds of sq. pt per byte;
	 * this file is at roughly 1,000,000 sq. pt/byte) and would still burn
	 * substantial CPU to rasterize despite passing the per-dimension check.
	 *
	 * @return void
	 */
	public function testDisproportionateMediaBoxIsRejected(): void
	{
		file_put_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log'), '');

		$started_at = microtime(true);
		$response = $this->uploadImage(TestConstants::SAMPLE_FILE_PDF_DISPROPORTIONATE_MEDIABOX);
		$elapsed = microtime(true) - $started_at;

		$photo = $response->json('photos.0');

		self::assertEquals(TestConstants::MIME_TYPE_APP_PDF, $photo['type']);
		self::assertNull( $photo['size_variants']['thumb']);
		self::assertNotEmpty(file_get_contents(storage_path('logs/daily-' . date('Y-m-d') . '.log')));
		self::assertLessThan(10, $elapsed);
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

			$response = $this->uploadImage(TestConstants::SAMPLE_FILE_TIFF);
			$photo = $response->json('photos.0');

			self::assertStringEndsWith('.tif', $photo['size_variants']['original']['url']);
			self::assertEquals(TestConstants::MIME_TYPE_IMG_TIFF, $photo['type']);
			self::assertNotNull($photo['size_variants']['thumb']);
		} finally {
			static::setAcceptedRawFormats($acceptedRawFormats);
		}
	}
}