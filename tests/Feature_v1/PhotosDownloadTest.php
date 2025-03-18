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
		$photo_upload_response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photo_archive_response = $this->photos_tests->download(
			[$photo_upload_response->offsetGet('id')], DownloadVariantType::ORIGINAL->value);

		// Stream the response in a temporary file
		$memory_blob = new InMemoryBuffer();
		fwrite($memory_blob->stream(), $photo_archive_response->streamedContent());
		$image_file = new TemporaryLocalFile('.jpg', 'night');
		$image_file->write($memory_blob->read());
		$memory_blob->close();

		// Just do a simple read test
		$image = new ImagickHandler();
		$image->load($image_file);
		$image_dim = $image->getDimensions();
		static::assertEquals(6720, $image_dim->width);
		static::assertEquals(4480, $image_dim->height);
	}

	/**
	 * Downloads an archive of two different photos.
	 *
	 * @return void
	 */
	public function testMultiplePhotoDownload(): void
	{
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photo_i_d2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');

		$photo_archive_response = $this->photos_tests->download(
			[$photo_i_d1, $photo_i_d2], DownloadVariantType::ORIGINAL->value);

		$zip_archive = AssertableZipArchive::createFromResponse($photo_archive_response);
		$zip_archive->assertContainsFilesExactly([
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

		$photo_upload_response = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_GMP_IMAGE)
		);
		$photo_archive_response = $this->photos_tests->download(
			[$photo_upload_response->offsetGet('id')],
			DownloadVariantType::LIVEPHOTOVIDEO->value
		);

		// Stream the response in a temporary file
		$memory_blob = new InMemoryBuffer();
		fwrite($memory_blob->stream(), $photo_archive_response->streamedContent());
		$video_file = new TemporaryLocalFile('.mov', 'gmp');
		$video_file->write($memory_blob->read());
		$memory_blob->close();

		// Just do a simple read test
		$video = new VideoHandler();
		$video->load($video_file);
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
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE)
		)->offsetGet('id');
		$photo_i_d2a = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$photo_i_d2b = $this->photos_tests->duplicate(
			[$photo_i_d2a], null
		)->json()[0]['id'];

		$photo_archive_response = $this->photos_tests->download([$photo_i_d1, $photo_i_d2a, $photo_i_d2b],
			DownloadVariantType::ORIGINAL->value);

		$zip_archive = AssertableZipArchive::createFromResponse($photo_archive_response);
		$zip_archive->assertContainsFilesExactly([
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
		$file_content = $download->streamedContent();
		self::assertEquals(file_get_contents(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE)), $file_content);
	}

	/**
	 * Downloads an archive of two different photos with one photo having
	 * a multi-byte file name.
	 *
	 * @return void
	 */
	public function testMultiplePhotoDownloadWithMultiByteFilename(): void
	{
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE)
		)->offsetGet('id');
		$photo_i_d2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');

		$photo_archive_response = $this->photos_tests->download([$photo_i_d1, $photo_i_d2], DownloadVariantType::ORIGINAL->value);

		$zip_archive = AssertableZipArchive::createFromResponse($photo_archive_response);
		$zip_archive->assertContainsFilesExactly([
			'fin de journée.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE))],
			'mongolia.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
	}

	public function testAlbumDownloadWithMultibyteTitle(): void
	{
		$album_i_d = $this->albums_tests->add(null, self::MULTI_BYTE_ALBUM_TITLE)->offsetGet('id');
		$this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE), $album_i_d
		);
		$this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $album_i_d
		);

		$album_archive_response = $this->albums_tests->download($album_i_d);
		$album_archive_response->assertHeader('Content-Type', 'application/x-zip');
		$album_archive_response->assertHeader('Content-Disposition', HeaderUtils::makeDisposition(
			HeaderUtils::DISPOSITION_ATTACHMENT,
			self::MULTI_BYTE_ALBUM_TITLE . '.zip',
			'Album.zip'
		));

		$zip_archive = AssertableZipArchive::createFromResponse($album_archive_response);
		$zip_archive->assertContainsFilesExactly([
			self::MULTI_BYTE_ALBUM_TITLE . '/fin de journée.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE))],
			self::MULTI_BYTE_ALBUM_TITLE . '/mongolia.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);

		$this->albums_tests->delete([$album_i_d]);
	}

	public function testDownloadOfInvisibleUnsortedPhotoByNonOwner(): void
	{
		Auth::loginUsingId(1);
		$user_i_d1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$user_i_d2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d1);
		$photo_i_d = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d2);
		$this->photos_tests->download([$photo_i_d], DownloadVariantType::ORIGINAL->value, 403);
	}

	public function testDownloadOfPhotoInSharedDownloadableAlbum(): void
	{
		$are_albums_downloadable = Configs::getValueAsBool(TestConstants::CONFIG_DOWNLOADABLE);
		try {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, true);
			Auth::loginUsingId(1);
			$user_i_d1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
			$user_i_d2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($user_i_d1);
			$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
			$photo_i_d = $this->photos_tests->upload(
				self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE),
				$album_i_d
			)->offsetGet('id');
			$this->sharing_tests->add([$album_i_d], [$user_i_d2]);
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($user_i_d2);
			$this->photos_tests->download([$photo_i_d], DownloadVariantType::ORIGINAL->value);
		} finally {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, $are_albums_downloadable);
		}
	}

	public function testDownloadOfPhotoInSharedNonDownloadableAlbum(): void
	{
		$are_albums_downloadable = Configs::getValueAsBool(TestConstants::CONFIG_DOWNLOADABLE);
		try {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, false);
			Auth::loginUsingId(1);
			$user_i_d1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
			$user_i_d2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($user_i_d1);
			$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
			$photo_i_d = $this->photos_tests->upload(
				self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE),
				$album_i_d
			)->offsetGet('id');
			$this->sharing_tests->add([$album_i_d], [$user_i_d2]);
			Auth::logout();
			Session::flush();
			Auth::loginUsingId($user_i_d2);
			$this->photos_tests->download([$photo_i_d], DownloadVariantType::ORIGINAL->value, 403);
		} finally {
			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, $are_albums_downloadable);
		}
	}
}
