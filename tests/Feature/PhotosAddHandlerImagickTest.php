<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use Tests\Feature\Traits\InteractsWithRaw;
use Tests\Feature\Traits\RequiresImageHandler;
use Tests\TestCase;

/**
 * Runs the tests of {@link PhotosAddHandlerTestAbstract} with Imagick as image handler.
 */
class PhotosAddHandlerImagickTest extends PhotosAddHandlerTestAbstract
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

			$photo = static::convertJsonToObject($this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TIFF)
			));

			static::assertStringEndsWith('.tif', $photo->size_variants->original->url);
			static::assertEquals(TestCase::MIME_TYPE_IMG_TIFF, $photo->type);
			static::assertNotNull($photo->size_variants->thumb);
		} finally {
			static::setAcceptedRawFormats($acceptedRawFormats);
		}
	}

	/**
	 * Tests uploading of an accepted PSD.
	 *
	 * As Imagick supports PSD, we also expect generated thumbnail.
	 *
	 * @return void
	 */
	public function testAcceptedPsdUpload(): void
	{
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_PSD)
		));
		static::assertStringEndsWith('.psd', $photo->size_variants->original->url);
		static::assertEquals(TestCase::MIME_TYPE_APP_PSD, $photo->type);
		static::assertNotNull($photo->size_variants->thumb);
	}
}
