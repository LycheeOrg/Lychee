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

use App\Image\MediaFile;
use App\Models\Configs;
use Tests\TestCase;

/**
 * Runs the tests of {@link PhotosAddTestAbstract} with GD as image handler.
 */
class PhotosAddGDTest extends PhotosAddTestAbstract
{
	protected int $hasImagickInit;

	public function setUp(): void
	{
		parent::setUp();

		$this->hasImagickInit = (int) Configs::get_value(self::CONFIG_HAS_IMAGICK, 0);
		Configs::set(self::CONFIG_HAS_IMAGICK, 0);

		if (Configs::hasImagick()) {
			static::markTestSkipped('Imagick still enabled although it shouldn\'t. Test Skipped.');
		}
	}

	public function tearDown(): void
	{
		Configs::set(self::CONFIG_HAS_IMAGICK, $this->hasImagickInit);
		parent::tearDown();
	}

	/**
	 * Tests uploading of an accepted XCF (GIMP image).
	 *
	 * As GD does not support XCFs, no thumbnail is generated.
	 * Nonetheless, the original file should be uploaded without error.
	 *
	 * @return void
	 */
	public function testAcceptedRawUpload(): void
	{
		$acceptedRawFormats = Configs::get_value(self::CONFIG_RAW_FORMATS, '');
		try {
			Configs::set(self::CONFIG_RAW_FORMATS, '.xcf');
			$reflection = new \ReflectionClass(MediaFile::class);
			$reflection->setStaticPropertyValue('cachedAcceptedRawFileExtensions', null);

			$photo = static::convertJsonToObject($this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_XCF)
			));

			static::assertStringEndsWith('.xcf', $photo->size_variants->original->url);
			static::assertNull($photo->size_variants->thumb);
		} finally {
			Configs::set(self::CONFIG_RAW_FORMATS, $acceptedRawFormats);
		}
	}
}
