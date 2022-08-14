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

use App\Facades\AccessControl;
use App\Models\Configs;
use App\SmartAlbums\UnsortedAlbum;
use Carbon\Carbon;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\RootAlbumUnitTest;
use Tests\Feature\Traits\InteractWithSmartAlbums;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\TestCase;

class GeoDataTest extends TestCase
{
	use RequiresEmptyPhotos;
	use RequiresEmptyAlbums;
	use InteractWithSmartAlbums;

	protected PhotosUnitTest $photos_tests;
	protected AlbumsUnitTest $albums_tests;
	protected RootAlbumUnitTest $root_album_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);
		$this->albums_tests = new AlbumsUnitTest($this);
		$this->root_album_tests = new RootAlbumUnitTest($this);

		AccessControl::log_as_id(0);

		$this->setUpRequiresEmptyPhotos();
		$this->setUpRequiresEmptyAlbums();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		AccessControl::logout();
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testGeo(): void
	{
		// save initial value
		$map_display_value = Configs::getValue(self::CONFIG_MAP_DISPLAY);

		try {
			$photoResponse = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
			);
			$photoID = $photoResponse->offsetGet('id');

			$this->albums_tests->get(UnsortedAlbum::ID, 200, $photoID);
			/*
			 * Check some Exif data
			 * The metadata extractor is unable to extract an explicit timezone
			 * for the test file.
			 * Hence, the attribute `taken_at` is relative to the default timezone
			 * of the application.
			 * Actually, the `exiftool` reports an attribute `Time Zone: +08:00`,
			 * if the tool is invoked from the command line, but the PHP wrapper
			 * \PHPExif\Exif does not use it.
			 */
			$taken_at = Carbon::create(
				2011, 8, 17, 16, 39, 37
			);
			$photoResponse->assertJson(
				[
					'id' => $photoID,
					'title' => 'mongolia',
					'type' => TestCase::MIME_TYPE_IMG_JPEG,
					'iso' => '200',
					'aperture' => 'f/13.0',
					'make' => 'NIKON CORPORATION',
					'model' => 'NIKON D5000',
					'shutter' => '1/640 s',
					'focal' => '44 mm',
					'altitude' => 1633,
					'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
					'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
					'is_public' => 0,
					'is_downloadable' => true,
					'is_share_button_visible' => true,
					'size_variants' => [
						'thumb' => [
							'width' => 200,
							'height' => 200,
						],
						'small' => [
							'width' => 542,
							'height' => 360,
						],
						'medium' => null,
						'medium2x' => null,
						'original' => [
							'width' => 1280,
							'height' => 850,
							'filesize' => 201316,
						],
					],
				]
			);

			$albumResponse = $this->albums_tests->add(null, 'test_mongolia');
			$albumID = $albumResponse->offsetGet('id');
			$this->photos_tests->set_album($albumID, [$photoID]);
			$this->clearCachedSmartAlbums();
			$this->albums_tests->get(UnsortedAlbum::ID, 200, null, $photoID);
			$albumResponse = $this->albums_tests->get($albumID);
			$album = static::convertJsonToObject($albumResponse);
			static::assertCount(1, $album->photos);
			static::assertEquals($photoID, $album->photos[0]->id);

			// now we test position Data

			// set to false
			Configs::set(self::CONFIG_MAP_DISPLAY, false);
			static::assertEquals(false, Configs::getValueAsBool(self::CONFIG_MAP_DISPLAY));
			$this->root_album_tests->getPositionData();

			// set to true
			Configs::set(self::CONFIG_MAP_DISPLAY, true);
			static::assertEquals(true, Configs::getValueAsBool(self::CONFIG_MAP_DISPLAY));
			$positionDataResponse = $this->root_album_tests->getPositionData();
			$positionData = static::convertJsonToObject($positionDataResponse);
			static::assertObjectHasAttribute('photos', $positionData);
			static::assertCount(1, $positionData->photos);
			static::assertEquals($photoID, $positionData->photos[0]->id);

			// set to false
			Configs::set(self::CONFIG_MAP_DISPLAY, false);
			static::assertEquals(false, Configs::getValueAsBool(self::CONFIG_MAP_DISPLAY));
			$this->albums_tests->getPositionData($albumID, false);

			// set to true
			Configs::set(self::CONFIG_MAP_DISPLAY, true);
			static::assertEquals(true, Configs::getValueAsBool(self::CONFIG_MAP_DISPLAY));
			$positionDataResponse = $this->albums_tests->getPositionData($albumID, false);
			$positionData = static::convertJsonToObject($positionDataResponse);
			static::assertObjectHasAttribute('photos', $positionData);
			static::assertCount(1, $positionData->photos);
			static::assertEquals($photoID, $positionData->photos[0]->id);
		} finally {
			Configs::set(self::CONFIG_MAP_DISPLAY, $map_display_value);
		}
	}
}
