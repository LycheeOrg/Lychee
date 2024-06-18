<?php

declare(strict_types=1);

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

use Tests\AbstractTestCase;
use Tests\Feature\Constants\TestConstants;
use Tests\Feature\Traits\InteractsWithRaw;
use Tests\Feature\Traits\RequiresImageHandler;

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

			$this->assertStringEndsWith('.tif', $photo->size_variants->original->url);
			$this->assertEquals(TestConstants::MIME_TYPE_IMG_TIFF, $photo->type);
			$this->assertNotNull($photo->size_variants->thumb);
		} finally {
			static::setAcceptedRawFormats($acceptedRawFormats);
		}
	}
}
