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

use App\Actions\Photo\Archive;
use App\Image\ImagickHandler;
use App\Image\InMemoryBuffer;
use App\Image\TemporaryLocalFile;
use App\Image\VideoHandler;
use function Safe\file_get_contents;
use function Safe\filesize;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Tests\Feature\Lib\AssertableZipArchive;
use Tests\TestCase;

class PhotosDownloadTest extends Base\PhotoTestBase
{
	public const MULTI_BYTE_ALBUM_TITLE = 'Lychee supporte les caractères multi-octets';

	/**
	 * Downloads a single photo.
	 *
	 * @return void
	 */
	public function testSinglePhotoDownload(): void
	{
		$photoUploadResponse = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photoArchiveResponse = $this->photos_tests->download([$photoUploadResponse->offsetGet('id')]);

		// Stream the response in a temporary file
		$memoryBlob = new InMemoryBuffer();
		fwrite($memoryBlob->stream(), $photoArchiveResponse->streamedContent());
		$imageFile = new TemporaryLocalFile('.jpg', 'night');
		$imageFile->write($memoryBlob->read());
		$memoryBlob->close();

		// Just do a simple read test
		$image = new ImagickHandler();
		$image->load($imageFile);
		$imageDim = $image->getDimensions();
		static::assertEquals(6720, $imageDim->width);
		static::assertEquals(4480, $imageDim->height);
	}

	/**
	 * Downloads an archive of two different photos.
	 *
	 * @return void
	 */
	public function testMultiplePhotoDownload(): void
	{
		$photoID1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');

		$photoArchiveResponse = $this->photos_tests->download([$photoID1, $photoID2]);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			'night.jpg' => ['size' => filesize(base_path(self::SAMPLE_FILE_NIGHT_IMAGE))],
			'mongolia.jpeg' => ['size' => filesize(base_path(self::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
	}

	/**
	 * Downloads the video part of a Google Motion Photo.
	 *
	 * @return void
	 */
	public function testGoogleMotionPhotoDownload(): void
	{
		static::assertHasExifToolOrSkip();
		static::assertHasFFMpegOrSkip();

		$photoUploadResponse = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_GMP_IMAGE)
		);
		$photoArchiveResponse = $this->photos_tests->download(
			[$photoUploadResponse->offsetGet('id')],
			Archive::LIVEPHOTOVIDEO
		);

		// Stream the response in a temporary file
		$memoryBlob = new InMemoryBuffer();
		fwrite($memoryBlob->stream(), $photoArchiveResponse->streamedContent());
		$videoFile = new TemporaryLocalFile('.mov', 'gmp');
		$videoFile->write($memoryBlob->read());
		$memoryBlob->close();

		// Just do a simple read test
		$video = new VideoHandler();
		$video->load($videoFile);
	}

	/**
	 * Downloads an archive of three photos with one photo being included twice.
	 *
	 * This tests the capability of the archive function to generate unique
	 * file names for duplicates.
	 *
	 * @return void
	 */
	public function testAmbiguousPhotoDownload(): void
	{
		$photoID1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_IMAGE)
		)->offsetGet('id');
		$photoID2a = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$photoID2b = $this->photos_tests->duplicate(
			[$photoID2a], null
		)->offsetGet('id');

		$photoArchiveResponse = $this->photos_tests->download([$photoID1, $photoID2a, $photoID2b]);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			'train.jpg' => ['size' => filesize(base_path(self::SAMPLE_FILE_TRAIN_IMAGE))],
			'mongolia-1.jpeg' => ['size' => filesize(base_path(self::SAMPLE_FILE_MONGOLIA_IMAGE))],
			'mongolia-2.jpeg' => ['size' => filesize(base_path(self::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
	}

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
		$photoID1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_SUNSET_IMAGE)
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');

		$photoArchiveResponse = $this->photos_tests->download([$photoID1, $photoID2]);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			'fin de journée.jpg' => ['size' => filesize(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE))],
			'mongolia.jpeg' => ['size' => filesize(base_path(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
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

		$zipArchive = AssertableZipArchive::createFromResponse($albumArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			self::MULTI_BYTE_ALBUM_TITLE . '/fin de journée.jpg' => ['size' => filesize(base_path(TestCase::SAMPLE_FILE_SUNSET_IMAGE))],
			self::MULTI_BYTE_ALBUM_TITLE . '/mongolia.jpeg' => ['size' => filesize(base_path(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);

		$this->albums_tests->delete([$albumID]);
	}
}
