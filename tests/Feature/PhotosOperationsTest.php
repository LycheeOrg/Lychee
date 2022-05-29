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
use App\Facades\AccessControl;
use App\Image\ImagickHandler;
use App\Image\InMemoryBuffer;
use App\Image\TemporaryLocalFile;
use App\Image\VideoHandler;
use App\Models\Configs;
use Carbon\Carbon;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresExifTool;
use Tests\Feature\Traits\RequiresFFMpeg;
use Tests\TestCase;
use ZipArchive;

class PhotosOperationsTest extends TestCase
{
	use RequiresFFMpeg;
	use RequiresExifTool;
	use RequiresEmptyPhotos;

	protected PhotosUnitTest $photos_tests;
	protected AlbumsUnitTest $albums_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);
		$this->albums_tests = new AlbumsUnitTest($this);

		$this->setUpRequiresExifTool();
		$this->setUpRequiresFFMpeg();
		$this->setUpRequiresEmptyPhotos();

		AccessControl::log_as_id(0);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresExifTool();
		$this->tearDownRequiresFFMpeg();

		AccessControl::logout();
		parent::tearDown();
	}

	/**
	 * Tests a lot of photo actions at once.
	 *
	 * This is 1:1 the old "upload" test.
	 * Preferably, all the tested actions should be seperated into individual tests.
	 *
	 * @return void
	 */
	public function testManyFunctionsAtOnce(): void
	{
		$id = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->get($id);

		$this->photos_tests->see_in_unsorted($id);
		$this->photos_tests->see_in_recent($id);
		$this->photos_tests->dont_see_in_shared($id);
		$this->photos_tests->dont_see_in_favorite($id);

		$this->photos_tests->set_title($id, "Night in Ploumanac'h");
		$this->photos_tests->set_description($id, 'A night photography');
		$this->photos_tests->set_star([$id], true);
		$this->photos_tests->set_tag([$id], ['night']);
		$this->photos_tests->set_public($id, true);
		$this->photos_tests->set_license($id, 'WTFPL', 422, 'The given data was invalid');
		$this->photos_tests->set_license($id, 'CC0');
		$this->photos_tests->set_license($id, 'CC-BY-1.0');
		$this->photos_tests->set_license($id, 'CC-BY-2.0');
		$this->photos_tests->set_license($id, 'CC-BY-2.5');
		$this->photos_tests->set_license($id, 'CC-BY-3.0');
		$this->photos_tests->set_license($id, 'CC-BY-4.0');
		$this->photos_tests->set_license($id, 'CC-BY-ND-1.0');
		$this->photos_tests->set_license($id, 'CC-BY-ND-2.0');
		$this->photos_tests->set_license($id, 'CC-BY-ND-2.5');
		$this->photos_tests->set_license($id, 'CC-BY-ND-3.0');
		$this->photos_tests->set_license($id, 'CC-BY-ND-4.0');
		$this->photos_tests->set_license($id, 'CC-BY-SA-1.0');
		$this->photos_tests->set_license($id, 'CC-BY-SA-2.0');
		$this->photos_tests->set_license($id, 'CC-BY-SA-2.5');
		$this->photos_tests->set_license($id, 'CC-BY-SA-3.0');
		$this->photos_tests->set_license($id, 'CC-BY-SA-4.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-1.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-2.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-2.5');
		$this->photos_tests->set_license($id, 'CC-BY-NC-3.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-4.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-ND-1.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-ND-2.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-ND-2.5');
		$this->photos_tests->set_license($id, 'CC-BY-NC-ND-3.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-ND-4.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-SA-1.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-SA-2.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-SA-2.5');
		$this->photos_tests->set_license($id, 'CC-BY-NC-SA-3.0');
		$this->photos_tests->set_license($id, 'CC-BY-NC-SA-4.0');
		$this->photos_tests->set_license($id, 'reserved');

		$this->photos_tests->see_in_favorite($id);
		$this->photos_tests->see_in_shared($id);
		$response = $this->photos_tests->get($id);

		/*
		 * Check some Exif data
		 */
		$taken_at = Carbon::create(
			2019, 6, 1, 1, 28, 25, '+02:00'
		);
		$response->assertJson([
			'album_id' => null,
			'id' => $id,
			'license' => 'reserved',
			'is_public' => 1,
			'is_starred' => true,
			'tags' => ['night'],
		]);

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->postJson('/api/Photo::getRandom');
		$response->assertOk();

		/*
		 * Erase tag
		 */
		$this->photos_tests->set_tag([$id], []);

		/**
		 * We now test interaction with albums.
		 */
		$albumID = $this->albums_tests->add(null, 'test_album_2')->offsetGet('id');
		$this->photos_tests->set_album('-1', [$id], 422);
		$this->photos_tests->set_album($albumID, [$id]);
		$this->albums_tests->download($albumID);
		$this->photos_tests->dont_see_in_unsorted($id);

		/**
		 * Test duplication, the duplicate should be completely identical
		 * except for the IDs.
		 */
		$response = $this->photos_tests->duplicate([$id], $albumID);
		$response->assertJson([
			'album_id' => $albumID,
			'aperture' => 'f/2.8',
			'description' => 'A night photography',
			'focal' => '16 mm',
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'license' => 'reserved',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'is_public' => 1,
			'shutter' => '30 s',
			'is_starred' => true,
			'tags' => [],
			'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
			'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
			'title' => "Night in Ploumanac'h",
			'type' => TestCase::MIME_TYPE_IMG_JPEG,
			'size_variants' => [
				'small' => [
					'width' => 540,
					'height' => 360,
				],
				'medium' => [
					'width' => 1620,
					'height' => 1080,
				],
				'original' => [
					'width' => 6720,
					'height' => 4480,
					'filesize' => 21106422,
				],
			],
		]);

		/**
		 * Get album which should contain both photos.
		 */
		$album = static::convertJsonToObject($this->albums_tests->get($albumID));
		static::assertCount(2, $album->photos);

		$ids = [];
		$ids[0] = $album->photos[0]->id;
		$ids[1] = $album->photos[1]->id;
		$this->photos_tests->delete([$ids[0]]);
		$this->photos_tests->get($ids[0], 404);

		$this->photos_tests->dont_see_in_recent($ids[0]);
		$this->photos_tests->dont_see_in_unsorted($ids[1]);

		$this->albums_tests->set_protection_policy($albumID);

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->postJson('/api/Photo::getRandom');
		$response->assertOk();

		// delete the picture after displaying it
		$this->photos_tests->delete([$ids[1]]);
		$this->photos_tests->get($ids[1], 404);
		$album = static::convertJsonToObject($this->albums_tests->get($albumID));
		static::assertCount(0, $album->photos);

		// save initial value
		$init_config_value = Configs::get_value('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '1');
		static::assertEquals('1', Configs::get_value('gen_demo_js'));

		// check redirection
		$response = $this->get('/demo');
		$response->assertOk();
		$response->assertViewIs('demo');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);

		$this->albums_tests->delete([$albumID]);

		$response = $this->postJson('/api/Photo::clearSymLink');
		$response->assertNoContent();
	}

	/**
	 * Repeats {@link PhotosOperationsTest::testManyFunctionsAtOnce()} with SL enabled.
	 *
	 * @return void
	 */
	public function testManyFunctionsAtOnceWithSL(): void
	{
		// save initial value
		$init_config_value1 = Configs::get_value('SL_enable');
		$init_config_value2 = Configs::get_value('SL_for_admin');

		try {
			// set to 1
			Configs::set('SL_enable', '1');
			Configs::set('SL_for_admin', '1');
			static::assertEquals('1', Configs::get_value('SL_enable'));
			static::assertEquals('1', Configs::get_value('SL_for_admin'));

			// just redo the test above :'D
			$this->testManyFunctionsAtOnce();
		} finally {
			// set back to initial value
			Configs::set('SL_enable', $init_config_value1);
			Configs::set('SL_for_admin', $init_config_value2);
		}
	}

	/**
	 * Runs a lot of negative tests at once.
	 *
	 * @return void
	 */
	public function testTrueNegative(): void
	{
		$this->photos_tests->get('-1', 422);
		$this->photos_tests->get('abcdefghijklmnopxyrstuvx', 404);
		$this->photos_tests->set_description('-1', 'test', 422);
		$this->photos_tests->set_description('abcdefghijklmnopxyrstuvx', 'test', 404);
		$this->photos_tests->set_public('-1', true, 422);
		$this->photos_tests->set_public('abcdefghijklmnopxyrstuvx', true, 404);
		$this->photos_tests->set_album('-1', ['-1'], 422);
		$this->photos_tests->set_album('abcdefghijklmnopxyrstuvx', ['-1'], 422);
		$this->photos_tests->set_album('-1', ['abcdefghijklmnopxyrstuvx'], 422);
		$this->photos_tests->set_album('abcdefghijklmnopxyrstuvx', ['abcdefghijklmnopxyrstuvx'], 404);
		$this->photos_tests->set_license('-1', 'CC0', 422);
		$this->photos_tests->set_license('abcdefghijklmnopxyrstuvx', 'CC0', 404);
	}

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
		$photoUploadResponse1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
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

		static::assertContains($fileStat1['name'], ['night.jpg', 'mongolia.jpeg']);
		static::assertContains($fileStat2['name'], ['night.jpg', 'mongolia.jpeg']);

		$expectedSize1 = $fileStat1['name'] === 'night.jpg' ? 21106422 : 201316;
		$expectedSize2 = $fileStat2['name'] === 'night.jpg' ? 21106422 : 201316;

		static::assertEquals($expectedSize1, $fileStat1['size']);
		static::assertEquals($expectedSize2, $fileStat2['size']);
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
	 * Downloads an archive of tree photos with one photo being included twice.
	 *
	 * This tests the capability of the archive function to generate unique
	 * file names for duplicates.
	 *
	 * @return void
	 */
	public function testAmbiguousPhotoDownload(): void
	{
		$photoUploadResponse1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_TRAIN_IMAGE)
		);
		$photoUploadResponse2 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		);
		$photoDuplicateResponse = $this->photos_tests->duplicate([$photoUploadResponse2->offsetGet('id')], null);

		$photoArchiveResponse = $this->photos_tests->download([
			$photoUploadResponse1->offsetGet('id'),
			$photoUploadResponse2->offsetGet('id'),
			$photoDuplicateResponse->offsetGet('id'),
		]);

		$memoryBlob = new InMemoryBuffer();
		fwrite($memoryBlob->stream(), $photoArchiveResponse->streamedContent());
		$tmpZipFile = new TemporaryLocalFile('.zip', 'archive');
		$tmpZipFile->write($memoryBlob->read());
		$memoryBlob->close();

		$zipArchive = new ZipArchive();
		$zipArchive->open($tmpZipFile->getRealPath());

		static::assertCount(3, $zipArchive);
		$fileStat1 = $zipArchive->statIndex(0);
		$fileStat2 = $zipArchive->statIndex(1);
		$fileStat3 = $zipArchive->statIndex(2);

		static::assertContains($fileStat1['name'], ['train.jpg', 'mongolia-1.jpeg', 'mongolia-2.jpeg']);
		static::assertContains($fileStat2['name'], ['train.jpg', 'mongolia-1.jpeg', 'mongolia-2.jpeg']);
		static::assertContains($fileStat3['name'], ['train.jpg', 'mongolia-1.jpeg', 'mongolia-2.jpeg']);

		$expectedSize1 = $fileStat1['name'] === 'train.jpg' ? 3478530 : 201316;
		$expectedSize2 = $fileStat2['name'] === 'train.jpg' ? 3478530 : 201316;
		$expectedSize3 = $fileStat3['name'] === 'train.jpg' ? 3478530 : 201316;

		static::assertEquals($expectedSize1, $fileStat1['size']);
		static::assertEquals($expectedSize2, $fileStat2['size']);
		static::assertEquals($expectedSize3, $fileStat3['size']);
	}
}
