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

use Tests\Feature\Base\PhotoTestBase;
use Tests\TestCase;

use function PHPUnit\Framework\assertTrue;

class PhotosMultiBytesTest extends PhotoTestBase
{

	public function testUploadDownloadMultibyte(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_SUNSET_IMAGE)
		)->offsetGet('id');

		$response = $this->photos_tests->get($id);
		$response->assertJson([
			'album_id' => NULL,
			'title' => 'fin de journée',
			'description' => NULL,
			'tags' => [],
			'license' => 'none',
			'is_public' => 0,
			'is_starred' => false,
			'iso' => '400',
			'make' => 'Canon',
			'model' => 'Canon EOS R5',
			'lens' => 'EF70-200mm f/2.8L IS USM',
			'aperture' => 'f/8.0',
			'shutter' => '1/320 s',
			'focal' => '200 mm',
			'type' => TestCase::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'small' => [
					'width' => 202,
					'height' => 360,
				],
				'medium' => [
					'width' => 607,
					'height' => 1080,
				],
				'original' => [
					'width' => 914,
					'height' => 1625,
					'filesize' => 270345,
				],
			],
		]);

		$this->photos_tests->download([$id]);
	}
}
