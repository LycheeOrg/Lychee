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

use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\NotImplementedException;
use App\Models\Configs;
use App\Models\Photo;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;
use Tests\Feature_v1\LibUnitTests\RootAlbumUnitTest;
use Tests\Feature_v1\LibUnitTests\SharingUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\InteractWithSmartAlbums;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyUsers;

class PhotosOperationsTest extends BasePhotoTest
{
	use InteractWithSmartAlbums;
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;

	protected RootAlbumUnitTest $root_album_tests;
	protected UsersUnitTest $users_tests;
	protected SharingUnitTest $sharing_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->root_album_tests = new RootAlbumUnitTest($this);
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
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->get($id);

		$this->clearCachedSmartAlbums();
		$this->albums_tests->get(UnsortedAlbum::ID, 200, $id);
		$this->albums_tests->get(RecentAlbum::ID, 200, $id);
		$this->albums_tests->get(StarredAlbum::ID, 200, null, $id);

		$this->photos_tests->set_title($id, "Night in Ploumanac'h");
		$this->photos_tests->set_description($id, 'A night photography');
		$this->photos_tests->set_star([$id], true);
		$this->photos_tests->set_tag([$id], ['night']);
		$this->photos_tests->set_tag([$id], ['trees'], false);
		$this->photos_tests->set_license($id, 'WTFPL', 422, 'The selected license is invalid.');
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

		/*
		 * Check some Exif data
		 */
		/** @var Carbon $taken_at */
		$taken_at = Carbon::create(
			2019,
			6,
			1,
			1,
			28,
			25,
			'+02:00'
		);

		$updated_taken_at = $taken_at->addYear();

		$this->photos_tests->set_upload_date($id, $updated_taken_at->toIso8601String());

		$this->clearCachedSmartAlbums();
		$this->albums_tests->get(StarredAlbum::ID, 200, $id);
		$response = $this->photos_tests->get($id);

		$response->assertJson([
			'album_id' => null,
			'id' => $id,
			'created_at' => $updated_taken_at->setTimezone('UTC')->format('Y-m-d\TH:i:sP'),
			'license' => 'reserved',
			'is_starred' => true,
			'tags' => ['night', 'trees'],
		]);

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->postJson('/api/Photo::getRandom');
		$this->assertOk($response);

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
		$this->clearCachedSmartAlbums();
		$this->albums_tests->get(UnsortedAlbum::ID, 200, null, $id);

		/**
		 * Test duplication, the duplicate should be completely identical
		 * except for the IDs.
		 */
		$response = $this->photos_tests->duplicate([$id], $albumID);
		$response->assertJson([
			0 => [
				'album_id' => $albumID,
				'aperture' => 'f/2.8',
				'description' => 'A night photography',
				'focal' => '16 mm',
				'iso' => '1250',
				'lens' => 'EF16-35mm f/2.8L USM',
				'license' => 'reserved',
				'make' => 'Canon',
				'model' => 'Canon EOS R',
				'shutter' => '30 s',
				'is_starred' => true,
				'tags' => [],
				'taken_at' => '2019-06-01T01:28:25+02:00',
				'taken_at_orig_tz' => '+02:00',
				'title' => "Night in Ploumanac'h",
				'type' => TestConstants::MIME_TYPE_IMG_JPEG,
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
			],
		]);

		/**
		 * Get album which should contain both photos.
		 */
		/** @var \App\Models\Album $album */
		$album = static::convertJsonToObject($this->albums_tests->get($albumID));
		static::assertCount(2, $album->photos);

		$ids = [];
		$ids[0] = $album->photos[0]->id;
		$ids[1] = $album->photos[1]->id;
		$this->photos_tests->delete([$ids[0]]);
		$this->photos_tests->get($ids[0], 404);

		$this->clearCachedSmartAlbums();
		$this->albums_tests->get(RecentAlbum::ID, 200, null, $ids[0]);
		$this->albums_tests->get(UnsortedAlbum::ID, 200, null, $ids[1]);

		$this->albums_tests->set_protection_policy($albumID);

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->postJson('/api/Photo::getRandom');
		$this->assertOk($response);

		// delete the picture after displaying it
		$this->photos_tests->delete([$ids[1]]);
		$this->photos_tests->get($ids[1], 404);
		/** @var \App\Models\Album $album */
		$album = static::convertJsonToObject($this->albums_tests->get($albumID));
		static::assertCount(0, $album->photos);
		$this->albums_tests->delete([$albumID]);

		$response = $this->postJson('/api/Photo::clearSymLink');
		$this->assertNoContent($response);
	}

	/**
	 * Repeats {@link PhotosOperationsTest::testManyFunctionsAtOnce()} with SL enabled.
	 *
	 * @return void
	 */
	public function testManyFunctionsAtOnceWithSL(): void
	{
		// save initial value
		$init_config_value1 = Configs::getValue('SL_enable');
		$init_config_value2 = Configs::getValue('SL_for_admin');

		try {
			// set to 1
			Configs::set('SL_enable', '1');
			Configs::set('SL_for_admin', '1');
			static::assertEquals('1', Configs::getValue('SL_enable'));
			static::assertEquals('1', Configs::getValue('SL_for_admin'));

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
		$this->photos_tests->set_album('-1', ['-1'], 422);
		$this->photos_tests->set_album('abcdefghijklmnopxyrstuvx', ['-1'], 422);
		$this->photos_tests->set_album('-1', ['abcdefghijklmnopxyrstuvx'], 422);
		$this->photos_tests->set_album('abcdefghijklmnopxyrstuvx', ['abcdefghijklmnopxyrstuvx'], 404);
		$this->photos_tests->set_license('-1', 'CC0', 422);
		$this->photos_tests->set_license('abcdefghijklmnopxyrstuvx', 'CC0', 404);
	}

	/**
	 * Tests that sub-albums show the correct thumbnail if displayed from
	 * within a hidden album.
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
		$albumSortingColumn = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_COL);
		$albumSortingOrder = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_ORDER);
		$photoSortingColumn = Configs::getValueAsString(TestConstants::CONFIG_PHOTOS_SORTING_COL);
		$photoSortingOrder = Configs::getValueAsString(TestConstants::CONFIG_PHOTOS_SORTING_ORDER);

		try {
			Auth::loginUsingId(1);
			RecentAlbum::getInstance()->setPublic();
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, true);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, 'title');
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');
			Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_COL, 'title');
			Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_ORDER, 'ASC');

			// Sic! This out-of-order creation of albums is on purpose in order to
			// catch errors where the album tree is accidentally ordered as
			// expected, because we created the albums in correct order
			$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
			$albumID12 = $this->albums_tests->add($albumID1, 'Test Album 1.2')->offsetGet('id');
			$albumID13 = $this->albums_tests->add($albumID1, 'Test Album 1.3')->offsetGet('id');
			$albumID121 = $this->albums_tests->add($albumID12, 'Test Album 1.2.1')->offsetGet('id');
			$albumID11 = $this->albums_tests->add($albumID1, 'Test Album 1.1')->offsetGet('id');

			$photoID11 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), $albumID11
			)->offsetGet('id');
			$photoID13 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID13
			)->offsetGet('id');
			$photoID12 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $albumID12
			)->offsetGet('id');
			$photoID121 = $this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_SUNSET_IMAGE), $albumID121
			)->offsetGet('id');

			$this->albums_tests->set_protection_policy(id: $albumID1, grants_full_photo_access: true, is_public: true, is_link_required: true);
			$this->albums_tests->set_protection_policy($albumID11);
			$this->albums_tests->set_protection_policy($albumID12);
			$this->albums_tests->set_protection_policy($albumID121);
			$this->albums_tests->set_protection_policy($albumID13);

			Auth::logout();
			Session::flush();
			$this->clearCachedSmartAlbums();

			// Check that Recent and root album show nothing to ensure
			// that we eventually really test the special searchability
			// condition for thumbnails within hidden albums do not
			// accidentally see the expected thumbnails, because we see them
			// anyway.

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
			]);
			foreach ([$albumID1, $photoID11, $photoID12, $photoID121, $photoID13] as $id) {
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

			// Access the hidden, but public albums and check whether we see
			// the correct thumbnails
			$responseForAlbum1 = $this->albums_tests->get($albumID1);
			$responseForAlbum1->assertJson([
				'id' => $albumID1,
				'parent_id' => null,
				'title' => 'Test Album 1',
				'thumb' => ['id' => $photoID121], // photo 1.2.1 "fin de journée" is alphabetically first
				'photos' => [],
				'albums' => [[
					'id' => $albumID11,
					'parent_id' => $albumID1,
					'title' => 'Test Album 1.1',
					'thumb' => ['id' => $photoID11],
				], [
					'id' => $albumID12,
					'parent_id' => $albumID1,
					'title' => 'Test Album 1.2',
					'thumb' => ['id' => $photoID121], // photo 1.2.1 "fin de journée" is alphabetically first
				], [
					'id' => $albumID13,
					'parent_id' => $albumID1,
					'title' => 'Test Album 1.3',
					'thumb' => ['id' => $photoID13],
				]],
			]);

			$responseForAlbum12 = $this->albums_tests->get($albumID12);
			$responseForAlbum12->assertJson([
				'id' => $albumID12,
				'parent_id' => $albumID1,
				'title' => 'Test Album 1.2',
				'thumb' => ['id' => $photoID121], // photo 1.2.1 "fin de journée" is alphabetically first
				'photos' => [[
					'id' => $photoID12,
					'album_id' => $albumID12,
					'title' => 'train',
				]],
				'albums' => [[
					'id' => $albumID121,
					'parent_id' => $albumID12,
					'title' => 'Test Album 1.2.1',
					'thumb' => ['id' => $photoID121],
				]],
			]);
		} finally {
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, $albumSortingColumn);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, $albumSortingOrder);
			Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_COL, $photoSortingColumn);
			Configs::set(TestConstants::CONFIG_PHOTOS_SORTING_ORDER, $photoSortingOrder);
			Configs::set(TestConstants::CONFIG_PUBLIC_SEARCH, $isPublicSearchEnabled);
			if ($isRecentPublic) {
				RecentAlbum::getInstance()->setPublic();
			} else {
				RecentAlbum::getInstance()->setPrivate();
			}
			Auth::logout();
			Session::flush();
		}
	}

	public function testDeleteMultiplePhotosByAnonUser(): void
	{
		Auth::loginUsingId(1);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $albumID
		)->offsetGet('id');
		$this->albums_tests->set_protection_policy($albumID);
		Auth::logout();
		Session::flush();
		$this->photos_tests->delete([$photoID1, $photoID2], 401);
	}

	public function testDeleteMultiplePhotosByNonOwner(): void
	{
		Auth::loginUsingId(1);
		$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID1);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_IMAGE), $albumID
		)->offsetGet('id');
		$this->sharing_tests->add([$albumID], [$userID2]);
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID2);
		$this->photos_tests->delete([$photoID1, $photoID2], 403);
	}

	/**
	 * Test setting legacy ID.
	 *
	 * @return void
	 */
	public function testNotImplemented(): void
	{
		$this->expectException(NotImplementedException::class);

		$photo = new Photo();
		$photo->legacy_id = 1234;
	}

	/**
	 * Test setting legacy ID.
	 *
	 * @return void
	 */
	public function testNotImplemented2(): void
	{
		$this->expectException(NotImplementedException::class);

		$photo = new Photo();
		$photo->id = '0000';
	}

	/**
	 * Test setting unsettable attributes.
	 *
	 * @return void
	 */
	public function testMustNotSet(): void
	{
		$this->expectException(IllegalOrderOfOperationException::class);

		$photo = new Photo();
		$photo->live_photo_url = 'Something';
	}

	/**
	 * Test uploading without timeData.
	 */
	public function testUploadTimeFromFileTimeNull(): void
	{
		Auth::loginUsingId(1);
		$this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE),
			albumID: null,
			expectedStatusCodes: 201,
			assertSee: null,
			fileLastModifiedTime: null);
	}
}
