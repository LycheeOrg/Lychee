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

use App\Models\Configs;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\LibUnitTests\AlbumsUnitTest;
use Tests\Feature_v1\LibUnitTests\PhotosUnitTest;
use Tests\Feature_v1\LibUnitTests\RootAlbumUnitTest;
use Tests\Traits\InteractWithSmartAlbums;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyPhotos;

class GeoDataTest extends AbstractTestCase
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

		Auth::loginUsingId(1);

		$this->setUpRequiresEmptyPhotos();
		$this->setUpRequiresEmptyAlbums();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		Auth::logout();
		Session::flush();
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testGeo(): void
	{
		// save initial value
		$map_display_value = Configs::getValue(TestConstants::CONFIG_MAP_DISPLAY);

		try {
			$photoResponse = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
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
			/** @var Carbon $taken_at */
			$taken_at = Carbon::create(
				2011, 8, 17, 16, 39, 37
			);
			$photoResponse->assertJson(
				[
					'id' => $photoID,
					'title' => 'mongolia',
					'type' => TestConstants::MIME_TYPE_IMG_JPEG,
					'iso' => '200',
					'aperture' => 'f/13.0',
					'make' => 'NIKON CORPORATION',
					'model' => 'NIKON D5000',
					'shutter' => '1/640 s',
					'focal' => '44 mm',
					'altitude' => 1633,
					'taken_at' => $taken_at->format('Y-m-d\TH:i:sP'),
					'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
					'rights' => [
						'can_download' => true,
					],
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
			/** @var \App\Models\Album $album */
			$album = static::convertJsonToObject($albumResponse);
			static::assertCount(1, $album->photos);
			static::assertEquals($photoID, $album->photos[0]->id);

			// now we test position Data

			// set to false
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY, false);
			static::assertEquals(false, Configs::getValueAsBool(TestConstants::CONFIG_MAP_DISPLAY));
			$this->root_album_tests->getPositionData();

			// set to true
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY, true);
			static::assertEquals(true, Configs::getValueAsBool(TestConstants::CONFIG_MAP_DISPLAY));
			$positionDataResponse = $this->root_album_tests->getPositionData();
			/** @var \App\Http\Resources\Collections\PositionDataResource $positionData */
			$positionData = static::convertJsonToObject($positionDataResponse);
			static::assertIsObject($positionData);
			static::assertTrue(property_exists($positionData, 'photos'));
			static::assertCount(1, $positionData->photos);
			static::assertEquals($photoID, $positionData->photos[0]->id);

			// set to false
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY, false);
			static::assertEquals(false, Configs::getValueAsBool(TestConstants::CONFIG_MAP_DISPLAY));
			$this->albums_tests->getPositionData($albumID, false);

			// set to true
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY, true);
			static::assertEquals(true, Configs::getValueAsBool(TestConstants::CONFIG_MAP_DISPLAY));
			$positionDataResponse = $this->albums_tests->getPositionData($albumID, false);
			/** @var \App\Http\Resources\Collections\PositionDataResource $positionData */
			$positionData = static::convertJsonToObject($positionDataResponse);
			static::assertIsObject($positionData);
			static::assertTrue(property_exists($positionData, 'photos'));
			static::assertCount(1, $positionData->photos);
			static::assertEquals($photoID, $positionData->photos[0]->id);
		} finally {
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY, $map_display_value);
		}
	}

	/**
	 * Tests that sub-albums return the correct positional data of their
	 * photos if displayed from within a hidden album.
	 *
	 * Normally, photos of albums which are not browseable are not searchable
	 * either, because there is no "clickable" path from the root the album.
	 * However, this is not true, if the user is already _within_ the hidden
	 * album.
	 * In this case the search "base" is the hidden album and photos within
	 * sub-albums are searched to determine the best thumb.
	 *
	 * @return void
	 */
	public function testThumbnailsInsideHiddenAlbum(): void
	{
		$isRecentPublic = RecentAlbum::getInstance()->public_permissions() !== null;
		$isPublicSearchEnabled = Configs::getValueAsBool(TestConstants::CONFIG_PUBLIC_SEARCH);
		$displayMap = Configs::getValueAsBool(TestConstants::CONFIG_MAP_DISPLAY);
		$displayMapPublicly = Configs::getValueAsBool(TestConstants::CONFIG_MAP_DISPLAY_PUBLIC);
		$includeSubAlbums = Configs::getValueAsBool(TestConstants::CONFIG_MAP_INCLUDE_SUBALBUMS);

		try {
			Auth::loginUsingId(1);
			RecentAlbum::getInstance()->setPublic();
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, true);
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY, true);
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY_PUBLIC, true);
			Configs::set(TestConstants::CONFIG_MAP_INCLUDE_SUBALBUMS, true);

			$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
			$albumID11 = $this->albums_tests->add($albumID1, 'Test Album 1.1')->offsetGet('id');
			$albumID12 = $this->albums_tests->add($albumID1, 'Test Album 1.2')->offsetGet('id');
			$albumID121 = $this->albums_tests->add($albumID12, 'Test Album 1.2.1')->offsetGet('id');
			$albumID13 = $this->albums_tests->add($albumID1, 'Test Album 1.3')->offsetGet('id');

			$photoID1 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_AARHUS), $albumID1
			)->offsetGet('id');
			$photoID11 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_ETTLINGEN), $albumID11
			)->offsetGet('id');
			$photoID12 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $albumID12
			)->offsetGet('id');
			$photoID121 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_HOCHUFERWEG), $albumID121
			)->offsetGet('id');
			$photoID13 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID13
			)->offsetGet('id');

			$this->albums_tests->set_protection_policy(id: $albumID1, grants_full_photo_access: true, is_public: true, is_link_required: true);
			// Sic! We do not make album 1.1 public to ensure that the
			// search filter does not include too much
			$this->albums_tests->set_protection_policy($albumID12);
			$this->albums_tests->set_protection_policy($albumID121);
			$this->albums_tests->set_protection_policy($albumID13);

			Auth::logout();
			Session::flush();
			$this->clearCachedSmartAlbums();

			// Check that Recent and root album show nothing to ensure
			// that we eventually really test the special searchability
			// condition for positional data within hidden albums and do not
			// accidentally see the expected data, because we see the
			// corresponding photos anyway.

			$responseForRoot = $this->root_album_tests->get();
			$responseForRoot->assertJson([
				'smart_albums' => [
					'recent' => ['thumb' => null],
				],
				'tag_albums' => [],
				'albums' => [],
				'shared_albums' => [],
			]);
			$responseForRoot->assertJsonMissing([
				'unsorted' => null,
				'starred' => null,
				'on_this_day' => null,
			]);
			foreach ([$albumID1, $photoID1, $photoID11, $photoID12, $photoID121, $photoID13] as $id) {
				$responseForRoot->assertJsonMissing(['id' => $id]);
			}

			$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
			$responseForRecent->assertJson([
				'thumb' => null,
				'photos' => [],
			]);
			foreach ([$photoID11, $photoID12, $photoID121, $photoID13] as $id) {
				$responseForRecent->assertJsonMissing(['id' => $id]);
			}

			// Fetch positional data for the hidden, but public albums and
			// check whether we see the correct thumbnails
			$response = $this->albums_tests->getPositionData($albumID1, false);
			$response->assertJson([
				'id' => $albumID1,
				'title' => 'Test Album 1',
				'photos' => [['id' => $photoID1, 'title' => 'aarhus']],
			]);
			foreach ([$photoID11, $photoID12, $photoID121, $photoID13] as $id) {
				$response->assertJsonMissing(['id' => $id]);
			}

			$response = $this->albums_tests->getPositionData($albumID1, true);
			$response->assertJson([
				'id' => $albumID1,
				'title' => 'Test Album 1',
			]);
			$response->assertJsonFragment(['id' => $photoID1, 'title' => 'aarhus']);
			$response->assertJsonFragment(['id' => $photoID12, 'title' => 'train']);
			$response->assertJsonFragment(['id' => $photoID121,	'title' => 'hochuferweg']);
			$response->assertJsonFragment(['id' => $photoID13, 'title' => 'mongolia']);
			$response->assertJsonMissing(['id' => $photoID11]); // photo 1.1 has not been made public

			$response = $this->albums_tests->getPositionData($albumID12, false);
			$response->assertJson([
				'id' => $albumID12,
				'title' => 'Test Album 1.2',
				'photos' => [['id' => $photoID12, 'title' => 'train']],
			]);
			foreach ([$photoID1, $photoID11, $photoID121, $photoID13] as $id) {
				$response->assertJsonMissing(['id' => $id]);
			}

			$response = $this->albums_tests->getPositionData($albumID12, true);
			$response->assertJson(['id' => $albumID12, 'title' => 'Test Album 1.2']);
			$response->assertJsonFragment(['id' => $photoID12, 'title' => 'train']);
			$response->assertJsonFragment(['id' => $photoID121,	'title' => 'hochuferweg']);
			foreach ([$photoID1, $photoID11, $photoID13] as $id) {
				$response->assertJsonMissing(['id' => $id]);
			}
		} finally {
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $isPublicSearchEnabled);
			if ($isRecentPublic) {
				RecentAlbum::getInstance()->setPublic();
			} else {
				RecentAlbum::getInstance()->setPrivate();
			}
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY, $displayMap);
			Configs::set(TestConstants::CONFIG_MAP_DISPLAY_PUBLIC, $displayMapPublicly);
			Configs::set(TestConstants::CONFIG_MAP_INCLUDE_SUBALBUMS, $includeSubAlbums);
			Auth::logout();
			Session::flush();
		}
	}
}
