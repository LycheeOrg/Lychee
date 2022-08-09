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

use App\Image\InMemoryBuffer;
use App\Image\TemporaryLocalFile;
use function Safe\file_get_contents;
use function Safe\filesize;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Tests\TestCase;
use ZipArchive;

class PhotosDownloadTest extends Base\PhotoTestBase
{
	public const MULTI_BYTE_ALBUM_TITLE = 'Lychee supporte les caractères multi-octets';

	public function testPhotoDownloadWithMultiByteFilename(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_SUNSET_IMAGE)
		)->offsetGet('id');

		$download = $this->photos_tests->download([$id]);
		$download->assertHeader('Content-Type', TestCase::MIME_TYPE_IMG_JPEG);
		$download->assertHeader('Content-Length', filesize(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE)));
		$download->assertHeader('Content-Disposition', HeaderUtils::makeDisposition(
			HeaderUtils::DISPOSITION_ATTACHMENT,
			'fin de journée.jpg',
			'Photo.jpg'
		));
		$fileContent = $download->streamedContent();
		self::assertEquals(file_get_contents(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE)), $fileContent);
	}

	/**
	 * Downloads an archive of two different photos with one photo having
	 * a multi-byte file name.
	 *
	 * @return void
	 */
	public function testMultiplePhotoDownloadWithMultiByteFilename(): void
	{
		$photoUploadResponse1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_SUNSET_IMAGE)
		);
		$photoUploadResponse2 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		);

		$photoArchiveResponse = $this->photos_tests->download([
			$photoUploadResponse1->offsetGet('id'),
			$photoUploadResponse2->offsetGet('id'),
		]);

		$memoryBlob = new InMemoryBuffer();
		fwrite($memoryBlob->stream(), $photoArchiveResponse->streamedContent());
		$tmpZipFile = new TemporaryLocalFile('.zip', 'archive');
		$tmpZipFile->write($memoryBlob->read());
		$memoryBlob->close();

		$zipArchive = new ZipArchive();
		$zipArchive->open($tmpZipFile->getRealPath());

		static::assertCount(2, $zipArchive);
		$fileStat1 = $zipArchive->statIndex(0);
		$fileStat2 = $zipArchive->statIndex(1);

		static::assertContains($fileStat1['name'], ['fin de journée.jpg', 'mongolia.jpeg']);
		static::assertContains($fileStat2['name'], ['fin de journée.jpg', 'mongolia.jpeg']);

		$expectedSize1 = $fileStat1['name'] === 'fin de journée.jpg' ? filesize(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE)) : filesize(base_path(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE));
		$expectedSize2 = $fileStat2['name'] === 'fin de journée.jpg' ? filesize(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE)) : filesize(base_path(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE));

		static::assertEquals($expectedSize1, $fileStat1['size']);
		static::assertEquals($expectedSize2, $fileStat2['size']);
	}

	public function testAlbumDownloadWithMultibyteTitle(): void
	{
		$albumID = $this->albums_tests->add(null, self::MULTI_BYTE_ALBUM_TITLE)->offsetGet('id');
		$this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_SUNSET_IMAGE), $albumID
		);
		$this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID
		);

		$albumArchiveResponse = $this->albums_tests->download($albumID);
		$albumArchiveResponse->assertHeader('Content-Type', 'application/x-zip');
		$albumArchiveResponse->assertHeader('Content-Disposition', HeaderUtils::makeDisposition(
			HeaderUtils::DISPOSITION_ATTACHMENT,
			self::MULTI_BYTE_ALBUM_TITLE . '.zip',
			'Album.zip'
		));

		$memoryBlob = new InMemoryBuffer();
		fwrite($memoryBlob->stream(), $albumArchiveResponse->streamedContent());
		$tmpZipFile = new TemporaryLocalFile('.zip', 'archive');
		$tmpZipFile->write($memoryBlob->read());
		$memoryBlob->close();

		$zipArchive = new ZipArchive();
		$zipArchive->open($tmpZipFile->getRealPath());

		static::assertCount(2, $zipArchive);
		$fileStat1 = $zipArchive->statIndex(0);
		$fileStat2 = $zipArchive->statIndex(1);

		static::assertContains($fileStat1['name'], [self::MULTI_BYTE_ALBUM_TITLE . '/fin de journée.jpg', self::MULTI_BYTE_ALBUM_TITLE . '/mongolia.jpeg']);
		static::assertContains($fileStat2['name'], [self::MULTI_BYTE_ALBUM_TITLE . '/fin de journée.jpg', self::MULTI_BYTE_ALBUM_TITLE . '/mongolia.jpeg']);

		$expectedSize1 = $fileStat1['name'] === self::MULTI_BYTE_ALBUM_TITLE . '/fin de journée.jpg' ? filesize(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE)) : filesize(base_path(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE));
		$expectedSize2 = $fileStat2['name'] === self::MULTI_BYTE_ALBUM_TITLE . '/fin de journée.jpg' ? filesize(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE)) : filesize(base_path(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE));

		static::assertEquals($expectedSize1, $fileStat1['size']);
		static::assertEquals($expectedSize2, $fileStat2['size']);
	}
}
