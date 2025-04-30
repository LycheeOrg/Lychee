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

namespace Tests\Feature_v2\Album;

use App\Enum\DownloadVariantType;
use App\Image\Files\InMemoryBuffer;
use App\Image\Files\TemporaryLocalFile;
use App\Image\Handlers\ImagickHandler;
use App\Image\Handlers\VideoHandler;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use function Safe\file_get_contents;
use function Safe\filesize;
use function Safe\fwrite;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresExifTool;
use Tests\Traits\RequiresFFMpeg;

class DownloadTest extends BaseApiWithDataTest
{
	use RequiresExifTool;
	use RequiresFFMpeg;

	public const MULTI_BYTE_ALBUM_TITLE = 'Lychee supporte les caractères multi-octets';

	protected function uploadImage(string $filename, string $album_id)
	{
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: $filename, album_id: $album_id);
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => $album_id]);
		$this->assertOk($response);

		return $response;
	}

	/**
	 * Downloads a single photo.
	 *
	 * @return void
	 */
	public function testSinglePhotoDownload(): void
	{
		$response = $this->uploadImage(filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album5->id);
		$photo = $response->json('resource.photos.0');

		$photoArchiveResponse = $this->download(
			photo_ids: [$photo['id']],
			kind: DownloadVariantType::ORIGINAL);

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
		$this->catchFailureSilence = [];
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE, album_id: $this->album5->id, file_name: TestConstants::PHOTO_NIGHT_TITLE . '.jpg');
		$this->assertCreated($response);
		$response = $this->actingAs($this->admin)->upload('Photo', filename: TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, album_id: $this->album5->id, file_name: TestConstants::PHOTO_MONGOLIA_TITLE . '.jpeg');
		$this->assertCreated($response);

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');

		$photoArchiveResponse = $this->download(
			photo_ids: [$response->json('resource.photos.0.id'), $response->json('resource.photos.1.id')],
			kind: DownloadVariantType::ORIGINAL);

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

		$response = $this->uploadImage(filename: TestConstants::SAMPLE_FILE_GMP_IMAGE, album_id: $this->album5->id);
		$photo = $response->json('resource.photos.0');

		$photoArchiveResponse = $this->download(
			photo_ids: [$photo['id']],
			kind: DownloadVariantType::LIVEPHOTOVIDEO
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
		$response = $this->actingAs($this->admin)->upload(
			'Photo',
			filename: TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE,
			album_id: $this->album5->id,
			file_name: TestConstants::PHOTO_MONGOLIA_TITLE . '.jpeg');
		$this->assertCreated($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$photo = $response->json('resource.photos.0');

		$this->postJson('Photo::copy', [
			'photo_ids' => [$photo['id']],
			'album_id' => $this->album5->id,
		]);
		$this->assertOk($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$response->assertJsonCount(2, 'resource.photos');

		$response = $this->actingAs($this->admin)->upload(
			'Photo',
			filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE,
			album_id: $this->album5->id,
			file_name: TestConstants::PHOTO_NIGHT_TITLE . '.jpg');
		$this->assertCreated($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$response->assertJsonCount(3, 'resource.photos');

		$photoID1 = $response->json('resource.photos.0.id');
		$photoID2a = $response->json('resource.photos.1.id');
		$photoID2b = $response->json('resource.photos.2.id');

		$photoArchiveResponse = $this->download([$photoID1, $photoID2a, $photoID2b], kind: DownloadVariantType::ORIGINAL);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			'night.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE))],
			'mongolia-1.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
			'mongolia-2.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
	}

	public function testPhotoDownloadWithMultiByteFilename(): void
	{
		$response = $this->actingAs($this->admin)->upload(
			'Photo',
			filename: TestConstants::SAMPLE_FILE_SUNSET_IMAGE,
			album_id: $this->album5->id,
			file_name: 'fin de journée.jpg');
		$this->assertCreated($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');

		$id = $response->json('resource.photos.0.id');

		$download = $this->download([$id], kind: DownloadVariantType::ORIGINAL);
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
	 * Downloads an archive of two different photos in different albums with one photo having
	 * a multi-byte file name.
	 *
	 * @return void
	 */
	public function testMultiplePhotoAlbumDownloadWithMultiByteFilename(): void
	{
		$response = $this->actingAs($this->admin)->upload(
			'Photo',
			filename: TestConstants::SAMPLE_FILE_SUNSET_IMAGE,
			album_id: $this->album5->id,
			file_name: 'fin de journée.jpg');
		$this->assertCreated($response);

		$album6 = Album::factory()->children_of($this->album5)->owned_by($this->admin)->create();

		$response = $this->actingAs($this->admin)->upload(
			'Photo',
			filename: TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE,
			album_id: $album6->id,
			file_name: TestConstants::PHOTO_MONGOLIA_TITLE . '.jpeg');
		$this->assertCreated($response);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album5->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');
		$response->assertJsonCount(1, 'resource.albums');
		$response = $this->getJsonWithData('Album', ['album_id' => $album6->id]);
		$this->assertOk($response);
		$response->assertJsonCount(1, 'resource.photos');

		$photoArchiveResponse = $this->download(album_ids: [$this->album5->id]);

		$zipArchive = AssertableZipArchive::createFromResponse($photoArchiveResponse);
		$zipArchive->assertContainsFilesExactly([
			$this->album5->title . '/fin de journée.jpg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_SUNSET_IMAGE))],
			$this->album5->title . '/' . $album6->title . '/mongolia.jpeg' => ['size' => filesize(base_path(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE))],
		]);
	}

	// 	public function testDownloadOfInvisibleUnsortedPhotoByNonOwner(): void
	// 	{
	// 		Auth::loginUsingId(1);
	// 		$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
	// 		$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
	// 		Auth::logout();
	// 		Session::flush();
	// 		Auth::loginUsingId($userID1);
	// 		$photoID = $this->photos_tests->upload(
	// 			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
	// 		)->offsetGet('id');
	// 		Auth::logout();
	// 		Session::flush();
	// 		Auth::loginUsingId($userID2);
	// 		$this->photos_tests->download([$photoID], DownloadVariantType::ORIGINAL->value, 403);
	// 	}

	// 	public function testDownloadOfPhotoInSharedDownloadableAlbum(): void
	// 	{
	// 		$areAlbumsDownloadable = Configs::getValueAsBool(TestConstants::CONFIG_DOWNLOADABLE);
	// 		try {
	// 			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, true);
	// 			Auth::loginUsingId(1);
	// 			$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
	// 			$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
	// 			Auth::logout();
	// 			Session::flush();
	// 			Auth::loginUsingId($userID1);
	// 			$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
	// 			$photoID = $this->photos_tests->upload(
	// 				self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE),
	// 				$albumID
	// 			)->offsetGet('id');
	// 			$this->sharing_tests->add([$albumID], [$userID2]);
	// 			Auth::logout();
	// 			Session::flush();
	// 			Auth::loginUsingId($userID2);
	// 			$this->photos_tests->download([$photoID], DownloadVariantType::ORIGINAL->value);
	// 		} finally {
	// 			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, $areAlbumsDownloadable);
	// 		}
	// 	}

	// 	public function testDownloadOfPhotoInSharedNonDownloadableAlbum(): void
	// 	{
	// 		$areAlbumsDownloadable = Configs::getValueAsBool(TestConstants::CONFIG_DOWNLOADABLE);
	// 		try {
	// 			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, false);
	// 			Auth::loginUsingId(1);
	// 			$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
	// 			$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
	// 			Auth::logout();
	// 			Session::flush();
	// 			Auth::loginUsingId($userID1);
	// 			$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
	// 			$photoID = $this->photos_tests->upload(
	// 				self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE),
	// 				$albumID
	// 			)->offsetGet('id');
	// 			$this->sharing_tests->add([$albumID], [$userID2]);
	// 			Auth::logout();
	// 			Session::flush();
	// 			Auth::loginUsingId($userID2);
	// 			$this->photos_tests->download([$photoID], DownloadVariantType::ORIGINAL->value, 403);
	// 		} finally {
	// 			Configs::set(TestConstants::CONFIG_DOWNLOADABLE, $areAlbumsDownloadable);
	// 		}
	// 	}
}