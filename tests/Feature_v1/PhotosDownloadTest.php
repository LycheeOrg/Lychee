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

use App\Enum\DownloadVariantType;
use App\Image\Files\InMemoryBuffer;
use App\Image\Files\TemporaryLocalFile;
use App\Image\Handlers\ImagickHandler;
use App\Image\Handlers\VideoHandler;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use function Safe\file_get_contents;
use function Safe\filesize;
use function Safe\fwrite;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\LibUnitTests\AssertableZipArchive;
use Tests\Feature_v1\LibUnitTests\SharingUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyUsers;

class PhotosDownloadTest extends Base\BasePhotoTest
{
	use RequiresEmptyUsers;
	use RequiresEmptyAlbums;

	public const MULTI_BYTE_ALBUM_TITLE = 'Lychee supporte les caractères multi-octets';

	protected UsersUnitTest $users_tests;
	protected SharingUnitTest $sharing_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->users_tests = new UsersUnitTest($this);
		$this->sharing_tests = new SharingUnitTest($this);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	/**
	 * Downloads a single photo.
	 *
	 * @return void
	 */
	public function testSinglePhotoDownload(): void
	{
		$photoUploadResponse = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photoArchiveResponse = $this->photos_tests->download(
			[$photoUploadResponse->offsetGet('id')], DownloadVariantType::ORIGINAL->value);

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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');

		$photoArchiveResponse = $this->photos_tests->download(
			[$photoID1, $photoID2], DownloadVariantType::ORIGINAL->value);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			'night.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE))],
			'mongolia.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GMP_IMAGE)
		);
		$photoArchiveResponse = $this->photos_tests->download(
			[$photoUploadResponse->offsetGet('id')],
			DownloadVariantType::LIVEPHOTOVIDEO->value
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE)
		)->offsetGet('id');
		$photoID2a = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$photoID2b = $this->photos_tests->duplicate(
			[$photoID2a], null
		)->json()[0]['id'];

		$photoArchiveResponse = $this->photos_tests->download([$photoID1, $photoID2a, $photoID2b],
			DownloadVariantType::ORIGINAL->value);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			'train.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_TRAIN_IMAGE))],
			'mongolia-1.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
			'mongolia-2.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
	}

	public function testPhotoDownloadWithMultiByteFilename(): void
	{
		$id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE)
		)->offsetGet('id');

		$download = $this->photos_tests->download([$id], DownloadVariantType::ORIGINAL->value);
		$download->assertHeader('Content-Type', TestConstants::MIME_TYPE_IMG_JPEG);
		$download->assertHeader('Content-Length', filesize(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE)));
		$download->assertHeader('Content-Disposition', HeaderUtils::makeDisposition(
			HeaderUtils::DISPOSITION_ATTACHMENT,
			'fin de journée.jpg',
			'Photo.jpg'
		));
		$fileContent = $download->streamedContent();
		self::assertEquals(file_get_contents(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE)), $fileContent);
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE)
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');

		$photoArchiveResponse = $this->photos_tests->download([$photoID1, $photoID2], DownloadVariantType::ORIGINAL->value);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			'fin de journée.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE))],
			'mongolia.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
	}

	public function testAlbumDownloadWithMultibyteTitle(): void
	{
		$albumID = $this->albums_tests->add(null, self::MULTI_BYTE_ALBUM_TITLE)->offsetGet('id');
		$this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE), $albumID
		);
		$this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID
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
			self::MULTI_BYTE_ALBUM_TITLE . '/fin de journée.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE))],
			self::MULTI_BYTE_ALBUM_TITLE . '/mongolia.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);

		$this->albums_tests->delete([$albumID]);
	}

	public function testDownloadOfInvisibleUnsortedPhotoByNonOwner(): void
	{
		Auth::loginUsingId(1);
		$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID1);
		$photoID = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID2);
		$this->photos_tests->download([$photoID], DownloadVariantType::ORIGINAL->value, 403);
	}

	public function testDownloadOfPhotoInSharedDownloadableAlbum(): void
	{
		$areAlbumsDownloadable = Configs::getValueAsBool(TestConstants::CONFIG_DOWNLOADABLE);
		try {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, true);
			Auth::loginUsingId(1);
			$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
			$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($userID1);
			$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
			$photoID = $this->photos_tests->upload(
				self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE),
				$albumID
			)->offsetGet('id');
			$this->sharing_tests->add([$albumID], [$userID2]);
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($userID2);
			$this->photos_tests->download([$photoID], DownloadVariantType::ORIGINAL->value);
		} finally {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, $areAlbumsDownloadable);
		}
	}

	public function testDownloadOfPhotoInSharedNonDownloadableAlbum(): void
	{
		$areAlbumsDownloadable = Configs::getValueAsBool(TestConstants::CONFIG_DOWNLOADABLE);
		try {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, false);
			Auth::loginUsingId(1);
			$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
			$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($userID1);
			$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
			$photoID = $this->photos_tests->upload(
				self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE),
				$albumID
			)->offsetGet('id');
			$this->sharing_tests->add([$albumID], [$userID2]);
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($userID2);
			$this->photos_tests->download([$photoID], DownloadVariantType::ORIGINAL->value, 403);
		} finally {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, $areAlbumsDownloadable);
		}
	}
}
