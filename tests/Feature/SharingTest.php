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
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Tests\Feature\Base\PhotoTestBase;
use Tests\Feature\Lib\RootAlbumUnitTest;
use Tests\Feature\Lib\SharingUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\Feature\Traits\InteractWithSmartAlbums;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\TestCase;

class SharingTest extends PhotoTestBase
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use InteractWithSmartAlbums;

	public const PHOTO_NIGHT_TITLE = 'night';
	public const PHOTO_MONGOLIA_TITLE = 'mongolia';
	public const PHOTO_TRAIN_TITLE = 'train';

	public const ALBUM_TITLE_1 = 'Test Album 1';
	public const ALBUM_TITLE_2 = 'Test Album 2';
	public const ALBUM_TITLE_3 = 'Test Album 3';

	public const ALBUM_PWD_1 = 'Album Password 1';
	public const ALBUM_PWD_2 = 'Album Password 2';
	public const ALBUM_PWD_3 = 'Album Password 3';

	public const USER_NAME_1 = 'Test User 1';
	public const USER_NAME_2 = 'Test User 2';
	public const USER_NAME_3 = 'Test User 3';

	public const USER_PWD_1 = 'User Password 1';
	public const USER_PWD_2 = 'User Password 2';
	public const USER_PWD_3 = 'User Password 3';

	/** @var array[] defines the expected JSON result for each sample image such that we can avoid repeating this again and again during the tests */
	public const EXPECTED_PHOTO_JSON = [
		TestCase::SAMPLE_FILE_NIGHT_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_NIGHT_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 6720, 'height' => 4480],
				'medium2x' => ['type' => 1, 'width' => 3240, 'height' => 2160],
				'medium' => ['type' => 2, 'width' => 1620, 'height' => 1080],
				'small2x' => ['type' => 3, 'width' => 1080,	'height' => 720],
				'small' => ['type' => 4, 'width' => 540, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
		TestCase::SAMPLE_FILE_MONGOLIA_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_MONGOLIA_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 1280, 'height' => 850],
				'medium2x' => null,
				'medium' => null,
				'small2x' => ['type' => 3, 'width' => 1084,	'height' => 720],
				'small' => ['type' => 4, 'width' => 542, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
		TestCase::SAMPLE_FILE_TRAIN_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_TRAIN_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 0, 'width' => 4032, 'height' => 3024],
				'medium2x' => ['type' => 1, 'width' => 2880, 'height' => 2160],
				'medium' => ['type' => 2, 'width' => 1440, 'height' => 1080],
				'small2x' => ['type' => 3, 'width' => 960,	'height' => 720],
				'small' => ['type' => 4, 'width' => 480, 'height' => 360],
				'thumb2x' => ['type' => 5, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 6, 'width' => 200, 'height' => 200],
			],
		],
	];

	protected SharingUnitTest $sharing_tests;
	protected UsersUnitTest $users_tests;
	protected RootAlbumUnitTest $root_album_tests;

	protected string $albumsSortingCol;
	protected string $albumsSortingOrder;
	protected string $photosSortingCol;
	protected string $photosSortingOrder;
	protected bool $isRecentAlbumPublic;
	protected bool $isStarredAlbumPublic;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyUsers();
		$this->sharing_tests = new SharingUnitTest($this);
		$this->users_tests = new UsersUnitTest($this);
		$this->root_album_tests = new RootAlbumUnitTest($this);

		// We must ensure a specific order of photos and albums otherwise
		// the test cannot reliably compare actual and expected values.
		// We sort by title, because tests are running so fast that using
		// creation time is not reliable as models get identical timestamps.
		// (This is not so much a problem for photos as photo processing
		// takes some time, but it is a problem for albums.)
		$this->albumsSortingCol = Configs::getValueAsString(TestCase::CONFIG_ALBUMS_SORTING_COL);
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_COL, 'title');
		$this->albumsSortingOrder = Configs::getValueAsString(TestCase::CONFIG_ALBUMS_SORTING_ORDER);
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');
		$this->photosSortingCol = Configs::getValueAsString(TestCase::CONFIG_PHOTOS_SORTING_COL);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_COL, 'title');
		$this->photosSortingOrder = Configs::getValueAsString(TestCase::CONFIG_PHOTOS_SORTING_ORDER);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_ORDER, 'ASC');

		$this->isRecentAlbumPublic = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_RECENT);
		Configs::set(TestCase::CONFIG_PUBLIC_RECENT, true);
		$this->isStarredAlbumPublic = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_STARRED);
		Configs::set(TestCase::CONFIG_PUBLIC_STARRED, true);
		$this->clearCachedSmartAlbums();
	}

	public function tearDown(): void
	{
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_COL, $this->albumsSortingCol);
		Configs::set(TestCase::CONFIG_ALBUMS_SORTING_ORDER, $this->albumsSortingOrder);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_COL, $this->photosSortingCol);
		Configs::set(TestCase::CONFIG_PHOTOS_SORTING_ORDER, $this->photosSortingOrder);

		Configs::set(TestCase::CONFIG_PUBLIC_RECENT, $this->isRecentAlbumPublic);
		Configs::set(TestCase::CONFIG_PUBLIC_STARRED, $this->isStarredAlbumPublic);
		$this->clearCachedSmartAlbums();

		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testEmptySharingList(): void
	{
		$response = $this->sharing_tests->list();
		$response->assertExactJson([
			'shared' => [],
			'albums' => [],
			'users' => [],
		]);
	}

	/**
	 * @return void
	 */
	public function testSharingListWithAlbums(): void
	{
		$albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add($albumID1, self::ALBUM_TITLE_2)->offsetGet('id');

		$response = $this->sharing_tests->list();
		$response->assertSimilarJson([
			'shared' => [],
			'albums' => [
				['id' => $albumID1, 'title' => self::ALBUM_TITLE_1],
				['id' => $albumID2, 'title' => self::ALBUM_TITLE_1 . '/' . self::ALBUM_TITLE_2],
			],
			'users' => [],
		]);
	}

	/**
	 * Adds albums and users, shares album with users and asserts that
	 * sharing list is correct.
	 *
	 * @return void
	 */
	public function testSharingListWithSharedAlbums(): void
	{
		$albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, self::ALBUM_TITLE_2)->offsetGet('id');
		$userID1 = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');
		$userID2 = $this->users_tests->add(self::USER_NAME_2, self::USER_PWD_2)->offsetGet('id');

		$this->sharing_tests->add([$albumID1], [$userID1]);
		$response = $this->sharing_tests->list();

		$response->assertJson([
			'shared' => [[
				'user_id' => $userID1,
				'album_id' => $albumID1,
				'username' => self::USER_NAME_1,
				'title' => self::ALBUM_TITLE_1,
			]],
			'albums' => [
				['id' => $albumID1, 'title' => self::ALBUM_TITLE_1],
				['id' => $albumID2, 'title' => self::ALBUM_TITLE_2],
			],
			'users' => [
				['id' => $userID1, 'username' => self::USER_NAME_1],
				['id' => $userID2, 'username' => self::USER_NAME_2],
			],
		]);
	}

	/**
	 * Creates two albums, puts a single photo in each, shares one
	 * album with a user, logs in as the user and checks that the user
	 * has proper access rights.
	 *
	 * In particular the following checks are made:
	 *  - the user sees the album (incl. its cover) but not the other album
	 *    nor image
	 *  - the user sees the image of the shared album in "Recent"
	 *  - the album tree contains the one album (incl. the photo) but not
	 *    the other one
	 *  - the user cannot access the non-shared album
	 *  - the user cannot access the non-shared photo
	 *
	 * @return void
	 */
	public function testAlbumSharedWithUser(): void
	{
		$albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, self::ALBUM_TITLE_2)->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID1)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $albumID2)->offsetGet('id');
		$userID = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');
		$this->sharing_tests->add([$albumID1], [$userID]);

		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => null],
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => ['thumb' => null],
				RecentAlbum::ID => ['thumb' => self::generateExpectedThumbJson($photoID1)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1, null, $photoID1),
			],
		]);
		$responseForRoot->assertJsonMissing(['id' => $albumID2]);
		$responseForRoot->assertJsonMissing(['title' => self::ALBUM_TITLE_2]);
		$responseForRoot->assertJsonMissing(['id' => $photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $photoID1, $albumID1, ['previous_photo_id' => null, 'next_photo_id' => null]),
			],
		]);
		$responseForRecent->assertJsonMissing(['id' => $albumID2]);
		$responseForRecent->assertJsonMissing(['id' => $photoID2]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1, null, $photoID1),
			],
		]);
		$responseForTree->assertJsonMissing(['id' => $albumID2]);
		$responseForTree->assertJsonMissing(['title' => self::ALBUM_TITLE_2]);
		$responseForTree->assertJsonMissing(['id' => $photoID2]);

		$this->albums_tests->get($albumID2, 403);
		$this->photos_tests->get($photoID2, 403);
	}

	/**
	 * Uploads a photo, logs out, checks that the anonymous user does not see
	 * the photo.
	 *
	 * In particular the following checks are made:
	 *  - the user does not see the photo in "Recent"
	 *  - the user cannot access the photo
	 *
	 * @return void
	 */
	public function testUnsortedPrivatePhotoWithAnonymousUser(): void
	{
		$photoID = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		AccessControl::logout();
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => null],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$this->photos_tests->get($photoID, 401);
	}

	/**
	 * Uploads a photo, logs in as another user, checks that the user
	 * does not see the photo.
	 *
	 * In particular the following checks are made:
	 *  - the user does not see the photo in "Unsorted"
	 *  - the user does not see the photo in "Recent"
	 *  - the user cannot access the photo
	 *
	 * @return void
	 */
	public function testUnsortedPrivatePhotoWithAuthenticatedUser(): void
	{
		$photoID = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		$userID = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');

		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => null],
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => ['thumb' => null],
				RecentAlbum::ID => ['thumb' => null],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson([
			'id' => UnsortedAlbum::ID,
			'title' => UnsortedAlbum::TITLE,
			'is_public' => false,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForUnsorted->assertJsonMissing(['id' => $photoID]);
		$responseForUnsorted->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$this->photos_tests->get($photoID, 403);
	}

	/**
	 * Ensure that searching public photos is disabled, uploads a photo,
	 * marks the photo as public and stars it, logs out and checks that the
	 * anonymous user does not see the photo even though it is public (but
	 * not searchable).
	 *
	 * In particular the following checks are made:
	 *  - the anonymous user does not see the public photo as the cover of
	 *    "Recent" and "Favorites"
	 *  - the anonymous user does not see the public photo inside "Recent" and
	 *    "Favorites"
	 *
	 * @return void
	 */
	public function testUnsortedPublicPhotoWithAnonymousUserAndNoPublicSearch(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, true);

		$photoID = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$this->photos_tests->set_public($photoID, true);
		$this->photos_tests->set_star([$photoID], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => null],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'id' => StarredAlbum::ID,
			'title' => StarredAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForStarred->assertJsonMissing(['id' => $photoID]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		// Even though public photo is not searchable and hence does not
		// show up in the smart albums, it can be fetched directly
		$this->photos_tests->get($photoID);

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
	}

	/**
	 * Ensure that searching public photos is enabled, uploads two photos,
	 * marks the alphabetically last photo as public and stars it, logs out
	 * and checks that the anonymous user sees the alphabetically last photo
	 * but not the other.
	 *
	 * In particular the following checks are made:
	 *  - the anonymous user sees the public photo as the cover of
	 *    "Recent" and "Favorites" (but not the other one which is the first
	 *    one in the alphabet)
	 *  - the anonymous user sees the public photo in "Recent" and
	 *    "Favorites" but not the other one
	 *
	 * @return void
	 */
	public function testUnsortedPublicPhotoWithAnonymousUserAndPublicSearch(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, false);

		$photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		$this->photos_tests->set_public($photoID1, true);
		$this->photos_tests->set_star([$photoID1], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $photoID1, null),
			],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID2]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'id' => StarredAlbum::ID,
			'title' => StarredAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $photoID1, null),
			],
		]);
		$responseForStarred->assertJsonMissing(['id' => $photoID2]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$this->photos_tests->get($photoID1);
		$this->photos_tests->get($photoID2, 401);

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
	}

	/**
	 * Ensure that searching public photos is disabled, uploads a photo,
	 * marks the photo as public and stars it, logs in as another user and
	 * checks that the user does not see the photo even though it is public
	 * (but not searchable).
	 *
	 * In particular the following checks are made:
	 *  - the user sees the public photo as the cover of
	 *    "Recent" and "Favorites" (but not the other one which is the first
	 *    one in the alphabet)
	 *  - the user sees the public photo in "Recent" and
	 *    "Favorites" but not the other one
	 *
	 * @return void
	 */
	public function testUnsortedPublicPhotoWithAuthenticatedUserAndNoPublicSearch(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, true);

		$photoID = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$userID = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');
		$this->photos_tests->set_public($photoID, true);
		$this->photos_tests->set_star([$photoID], true);

		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => null],
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => ['thumb' => null],
				RecentAlbum::ID => ['thumb' => null],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson([
			'id' => UnsortedAlbum::ID,
			'title' => UnsortedAlbum::TITLE,
			'is_public' => false,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForUnsorted->assertJsonMissing(['id' => $photoID]);
		$responseForUnsorted->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'id' => StarredAlbum::ID,
			'title' => StarredAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForStarred->assertJsonMissing(['id' => $photoID]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		$this->photos_tests->get($photoID);

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
	}

	/**
	 * Ensure that searching public photos is enabled, uploads two photos,
	 * marks the alphabetically last photo as public and stars it, logs in as
	 * another user and checks that the user sees the alphabetically last
	 * photo but not the other.
	 *
	 * In particular the following checks are made:
	 *  - the user sees the public photo as the cover of
	 *    "Recent" and "Favorites" (but not the other one which is the first
	 *    one in the alphabet)
	 *  - the user sees the public photo in "Recent" and
	 *    "Favorites" but not the other one
	 *
	 * @return void
	 */
	public function testUnsortedPublicPhotoWithAuthenticatedUserAndPublicSearch(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, false);

		$photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE))->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE))->offsetGet('id');
		$userID = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');
		$this->photos_tests->set_public($photoID1, true);
		$this->photos_tests->set_star([$photoID1], true);

		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
				PublicAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID2]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson([
			'id' => UnsortedAlbum::ID,
			'title' => UnsortedAlbum::TITLE,
			'is_public' => false,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $photoID1, null),
			],
		]);
		$responseForUnsorted->assertJsonMissing(['id' => $photoID2]);
		$responseForUnsorted->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $photoID1, null),
			],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID2]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'id' => StarredAlbum::ID,
			'title' => StarredAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $photoID1, null),
			],
		]);
		$responseForStarred->assertJsonMissing(['id' => $photoID2]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$this->photos_tests->get($photoID1);
		$this->photos_tests->get($photoID2, 403);

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
	}

	/**
	 * Ensure that searching public photos is disabled, creates an album
	 * with a photo, marks the photo as public and stars it, logs out and
	 * checks that the anonymous user does not see the photo even though it
	 * is public (but not searchable).
	 *
	 * This test is similar to
	 * {@link SharingTest::testUnsortedPublicPhotoWithAnonymousUserAndNoPublicSearch()}
	 * but with an album.
	 *
	 * @return void
	 */
	public function testPublicPhotoInPrivateAlbumWithAnonymousUserAndNoPublicSearch(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, true);

		$albumID = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$photoID = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $albumID)->offsetGet('id');
		$this->photos_tests->set_public($photoID, true);
		$this->photos_tests->set_star([$photoID], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => null],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'id' => StarredAlbum::ID,
			'title' => StarredAlbum::TITLE,
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForStarred->assertJsonMissing(['id' => $photoID]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);

		$this->albums_tests->get($albumID, 401);

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
	}

	/**
	 * Ensure that searching public photos is enabled, creates an album
	 * with two photos, marks the alphabetically last photo as public and
	 * stars it, logs out and checks that the anonymous user sees the
	 * alphabetically last photo but not the other.
	 *
	 * This test is similar to
	 * {@link SharingTest::testUnsortedPublicPhotoWithAnonymousUserAndPublicSearch()}
	 * but with an album.
	 *
	 * @return void
	 */
	public function testPublicPhotoInPrivateAlbumWithAnonymousUserAndPublicSearch(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, false);

		$albumID = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $albumID)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID)->offsetGet('id');
		$this->photos_tests->set_public($photoID1, true);
		$this->photos_tests->set_star([$photoID1], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $photoID1, $albumID),
			],
		]);
		$responseForRecent->assertJsonMissing(['id' => $photoID2]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'id' => StarredAlbum::ID,
			'title' => StarredAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $photoID1, $albumID),
			],
		]);
		$responseForStarred->assertJsonMissing(['id' => $photoID2]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		// The album and photo 2 are not accessible, but photo 1 is
		// because it is public even though it is contained in an inaccessible
		// album
		$this->albums_tests->get($albumID, 401);
		$this->photos_tests->get($photoID1);
		$this->photos_tests->get($photoID2, 401);

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
	}

	/**
	 * Ensure that searching public photos is disabled, creates an album
	 * with a photo, marks the album as public and stars the photo, logs out
	 * and checks that the anonymous user sees the photo.
	 *
	 * In comparison to
	 * {@link SharingTest::testUnsortedPublicPhotoWithAnonymousUserAndNoPublicSearch()}
	 * and
	 * {@link SharingTest::testPublicPhotoInPrivateAlbumWithAnonymousUserAndNoPublicSearch()}
	 * the photo is visible, although public search is disabled, because
	 * the photo is inside a public album which is browsable.
	 *
	 * Note that the setting "public search" only affects photos which are
	 * made public explicitly.
	 *
	 * @return void
	 */
	public function testPhotoInPublicAlbumWithAnonymousUserAndNoPublicSearch(): void
	{
		$arePublicPhotosHidden = Configs::getValueAsBool(TestCase::CONFIG_PUBLIC_HIDDEN);
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, true);

		$albumID = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$photoID = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $albumID)->offsetGet('id');
		$this->albums_tests->set_protection_policy($albumID);
		$this->photos_tests->set_star([$photoID], true);
		AccessControl::logout();
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID)],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID)],
			],
			'tag_albums' => [],
			'albums' => [
				$this->generateExpectedAlbumJson($albumID, self::ALBUM_TITLE_1, null, $photoID),
			],
			'shared_albums' => [],
		]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $photoID, $albumID),
			],
		]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'id' => StarredAlbum::ID,
			'title' => StarredAlbum::TITLE,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $photoID, $albumID),
			],
		]);

		$responseForAlbum = $this->albums_tests->get($albumID);
		$responseForAlbum->assertJson([
			'id' => $albumID,
			'title' => self::ALBUM_TITLE_1,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($photoID),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $photoID, $albumID),
			],
		]);

		// Even though the photo is not public by itself, it is visible
		// because it is in a public album
		$this->albums_tests->get($albumID);
		$this->photos_tests->get($photoID);

		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, $arePublicPhotosHidden);
	}

	/**
	 * Uploads an unsorted photo and another photo into an album,
	 * marks the unsorted photo as public, shares the album with another user,
	 * logs out, check that the anonymous user only sees the public photo,
	 * logs in as the other user and checks that both photos are visible.
	 *
	 * In particular the following checks are made:
	 *  - the anonymous user only sees the public photo in "Recent"
	 *    (inside and as a cover)
	 *  - the other user sees both photos in "Recent" and the shared photo
	 *    as cover (because it is more recent)
	 *
	 * @return void
	 */
	public function testPublicPhotoAndSharedAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Uploads two photos into two albums (one photo per album), marks one
	 * album as public and the other one as password-protected,
	 * logs out, checks that the anonymous user only sees both albums,
	 * but only the cover of the public one, provide password, checks that
	 * now both covers are visible.
	 *
	 * In particular the following checks are made:
	 *  - before the password has been provided the anonymous user only sees
	 *    the public photo
	 *     - as a cover of the public album
	 *     - in "Recent"
	 *     - in the album tree
	 *  - after the password has been provided the anonymous user sees both
	 *    photos
	 *     - as covers
	 *     - in "Recent"
	 *     - in the album tree
	 *
	 * @return void
	 */
	public function testPublicAlbumAndPasswordProtectedAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Like {@link SharingTest::testPublicAlbumAndPasswordProtectedAlbum},
	 * but additionally the password-protected photo is starred and the
	 * "Favorites" album is tested as well.
	 *
	 * @return void
	 */
	public function testPublicAlbumAndPasswordProtectedAlbumWithStarredPhoto(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Uploads two photos into two albums (one photo per album), marks one
	 * album as public and the other one as public and hidden,
	 * logs out, checks that the anonymous user only see the first album,
	 * accesses the second album and checks again that the anonymous user
	 * still only sees the first album.
	 *
	 * In particular the following checks are made:
	 *  - before and after the hidden album has been accessed, the anonymous
	 *    user only sees the public, not hidden photo
	 *     - as a cover of the public album
	 *     - in "Recent"
	 *     - in the album tree
	 *
	 * @return void
	 */
	public function testPublicAlbumAndHiddenAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Like {@link SharingTest::testPublicAlbumAndHiddenAlbum}, but
	 * additionally the hidden album is also password protected.
	 *
	 * @return void
	 */
	public function testPublicAlbumAndHiddenPasswordProtectedAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Tests six albums with one photo each and varying protection settings.
	 *
	 * This is the test for [Bug #1155](https://github.com/LycheeOrg/Lychee/issues/1155).
	 * Scenario:
	 *
	 * ```
	 *  A       (public, password-protected "foo")
	 *  |
	 *  +-- B   (public)
	 *  |
	 *  +-- C   (public, password-protected "foo")
	 *  |
	 *  +-- D   (public, password-protected "foo", hidden)
	 *  |
	 *  +-- E   (public, password-protected "bar")
	 *  |
	 *  +-- F   (public, password-protected "bar", hidden)
	 * ```
	 *
	 * The anonymous user proceeds as follows:
	 *
	 *  1. Get root album view
	 *
	 *     _Expected result:_ Album A is visible, but without cover, it is still locked
	 *
	 *  2. Unlock albums with password "foo"
	 *
	 *     _Expected result:_
	 *      - Album A is visible with cover
	 *      - Album B is visible with cover
	 *      - Album C is visible with cover, as it became unlocked simultaneously
	 *      - Album D remains invisible
	 *      - Album E is visible without cover, as it is still locked
	 *      - Album F remains invisible
	 *
	 *  3. Directly access album D
	 *
	 *     _Expected result:_
	 *      - Access is granted without asking for a password as it has already been unlocked
	 *      - Image inside D is visible as part of D, but nowhere else
	 *
	 *  4. Directly access album F
	 *
	 *     _Expected result:_ Access is denied
	 *
	 *  5. Unlock albums with password "bar"
	 *
	 *     _Expected result:_
	 *      - Album A is visible with cover
	 *      - Album B is visible with cover
	 *      - Album C is visible with cover, as it became unlocked simultaneously
	 *      - Album D remains invisible
	 *      - Album E is visible with cover, as it became unlocked simultaneously
	 *      - Album F remains invisible
	 *
	 *  6. Directly access album F
	 *
	 *     _Expected result:_
	 *      - Access is granted without asking for a password as it has already been unlocked
	 *      - Image inside F is visible as part of F, but nowhere else
	 *
	 * In particular, each visibility check includes
	 *  - the content inside the album itself
	 *  - the album "Recent"
	 *  - the album tree
	 *
	 * @return void
	 */
	public function testSixAlbumsWithDifferentProtectionSettings(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Create an album, upload a photo, share it with a user, mark it as
	 * public and password protected, login as user, access album, logout,
	 * provide password, access album.
	 *
	 * This test asserts that a sharing an album with a user takes
	 * precedence over password protection.
	 *
	 * @return void
	 */
	public function testSharedPasswordProtectedAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Create three albums with on photo each, share the first and second with
	 * a user, mark the second and third as public and password protected
	 * with the same password, login as user, check that album 1 and 2 are
	 * visible, unlock album 3, check that all albums are visible.
	 *
	 * In particular, each visibility check includes
	 *  - the content inside the album itself
	 *  - the album "Recent"
	 *  - the album tree
	 *
	 * This test asserts that unlocking an album does not badly interfere
	 * with another album which is shared but also happens to be protected
	 * by the same password.
	 *
	 * @return void
	 */
	public function testThreeAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		// PREPARATION

		$albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, self::ALBUM_TITLE_2)->offsetGet('id');
		$albumID3 = $this->albums_tests->add(null, self::ALBUM_TITLE_3)->offsetGet('id');
		// The mis-order of photos by there title (N, T, M) is on purpose such that
		// we first receive the result (N, T) as long as album 3 is locked and
		// then (M, N, T) after album 3 has been unlocked, with album 3
		// being in the front position.
		$photoID1 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_NIGHT_IMAGE), $albumID1)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_TRAIN_IMAGE), $albumID2)->offsetGet('id');
		$photoID3 = $this->photos_tests->upload(static::createUploadedFile(static::SAMPLE_FILE_MONGOLIA_IMAGE), $albumID3)->offsetGet('id');
		$userID = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');
		$this->sharing_tests->add([$albumID1, $albumID2], [$userID]);
		// Sic! We use the same password for both albums here, because we want
		// to ensure that incidentally "unlocking" an album which is also
		// shared has no negative side effect.
		$this->albums_tests->set_protection_policy(
			$albumID2, true, true, false, true, true, true, self::ALBUM_PWD_1
		);
		$this->albums_tests->set_protection_policy(
			$albumID3, true, true, false, true, true, true, self::ALBUM_PWD_1
		);

		// BEFORE UNLOCKING: ENSURE 1&2 ARE VISIBLE BUT NOT 3

		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => null],
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => ['thumb' => null],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID1)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1, null, $photoID1),
				self::generateExpectedAlbumJson($albumID2, self::ALBUM_TITLE_2, null, $photoID2),
				self::generateExpectedAlbumJson($albumID3, self::ALBUM_TITLE_3),
			],
		]);
		$responseForRoot->assertJsonMissing(['id' => $photoID3]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_NIGHT_IMAGE, $photoID1, $albumID1, ['previous_photo_id' => $photoID2, 'next_photo_id' => $photoID2]),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $photoID2, $albumID2, ['previous_photo_id' => $photoID1, 'next_photo_id' => $photoID1]),
			],
		]);
		$responseForRecent->assertJsonMissing(['id' => $albumID3]);
		$responseForRecent->assertJsonMissing(['id' => $photoID3]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1, null, $photoID1),
				self::generateExpectedAlbumJson($albumID2, self::ALBUM_TITLE_2, null, $photoID2),
			],
		]);
		$responseForTree->assertJsonMissing(['id' => $albumID3]);
		$responseForTree->assertJsonMissing(['title' => self::ALBUM_TITLE_3]);
		$responseForTree->assertJsonMissing(['id' => $photoID3]);

		$responseForAlbum1 = $this->albums_tests->get($albumID1);
		$responseForAlbum1->assertJson([
			'id' => $albumID1,
			'title' => self::ALBUM_TITLE_1,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_NIGHT_IMAGE, $photoID1, $albumID1),
			],
		]);

		$responseForAlbum2 = $this->albums_tests->get($albumID2);
		$responseForAlbum2->assertJson([
			'id' => $albumID2,
			'title' => self::ALBUM_TITLE_2,
			'thumb' => $this->generateExpectedThumbJson($photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $photoID2, $albumID2),
			],
		]);

		$this->albums_tests->get($albumID3, 403);
		$this->photos_tests->get($photoID3, 403);

		// AFTER UNLOCKING: ENSURE 1&2&3 ARE VISIBLE

		$this->albums_tests->unlock($albumID3, self::ALBUM_PWD_1);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => null],
				StarredAlbum::ID => ['thumb' => null],
				PublicAlbum::ID => ['thumb' => null],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($photoID3)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1, null, $photoID1),
				self::generateExpectedAlbumJson($albumID2, self::ALBUM_TITLE_2, null, $photoID2),
				self::generateExpectedAlbumJson($albumID3, self::ALBUM_TITLE_3, null, $photoID3),
			],
		]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'id' => RecentAlbum::ID,
			'title' => RecentAlbum::TITLE,
			'thumb' => $this->generateExpectedThumbJson($photoID3),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $photoID3, $albumID3, ['previous_photo_id' => $photoID2, 'next_photo_id' => $photoID1]),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_NIGHT_IMAGE, $photoID1, $albumID1, ['previous_photo_id' => $photoID3, 'next_photo_id' => $photoID2]),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $photoID2, $albumID2, ['previous_photo_id' => $photoID1, 'next_photo_id' => $photoID3]),
			],
		]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($albumID1, self::ALBUM_TITLE_1, null, $photoID1),
				self::generateExpectedAlbumJson($albumID2, self::ALBUM_TITLE_2, null, $photoID2),
				self::generateExpectedAlbumJson($albumID3, self::ALBUM_TITLE_3, null, $photoID3),
			],
		]);

		$responseForAlbum1 = $this->albums_tests->get($albumID1);
		$responseForAlbum1->assertJson([
			'id' => $albumID1,
			'title' => self::ALBUM_TITLE_1,
			'thumb' => $this->generateExpectedThumbJson($photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_NIGHT_IMAGE, $photoID1, $albumID1),
			],
		]);

		$responseForAlbum2 = $this->albums_tests->get($albumID2);
		$responseForAlbum2->assertJson([
			'id' => $albumID2,
			'title' => self::ALBUM_TITLE_2,
			'thumb' => $this->generateExpectedThumbJson($photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $photoID2, $albumID2),
			],
		]);

		$responseForAlbum3 = $this->albums_tests->get($albumID3);
		$responseForAlbum3->assertJson([
			'id' => $albumID3,
			'title' => self::ALBUM_TITLE_3,
			'thumb' => $this->generateExpectedThumbJson($photoID3),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $photoID3, $albumID3),
			],
		]);
	}

	/**
	 * Returns a JSON description of a photo.
	 *
	 * This is an internal helper method to avoid repeating the same
	 * cumbersome, expected JSON description again and again.
	 *
	 * @param string      $samplePhotoID the identifier of the sample photo:
	 *                                   {@link TestCase::SAMPLE_FILE_NIGHT_IMAGE},
	 *                                   {@link TestCase::SAMPLE_FILE_MONGOLIA_IMAGE}, or
	 *                                   {@link TestCase::SAMPLE_FILE_TRAIN_IMAGE}
	 * @param string      $photoID       the photo ID
	 * @param string|null $albumID       the album ID
	 * @param array       $attrToMerge   additional attributes which should be
	 *                                   merged into the generated JSON
	 *
	 * @return array
	 */
	private function generateExpectedPhotoJson(string $samplePhotoID, string $photoID, ?string $albumID, array $attrToMerge = []): array
	{
		$json = self::EXPECTED_PHOTO_JSON[$samplePhotoID];
		$json['id'] = $photoID;
		$json['album_id'] = $albumID;

		return array_merge_recursive($json, $attrToMerge);
	}

	private function generateExpectedThumbJson(?string $photoID): array|null
	{
		return $photoID === null ? null : ['id' => $photoID, 'type' => 'image/jpeg'];
	}

	private function generateExpectedAlbumJson(string $albumID, string $albumTitle, ?string $parentAlbumID = null, ?string $thumbID = null, array $attrToMerge = []): array
	{
		return array_merge_recursive([
			'id' => $albumID,
			'title' => $albumTitle,
			'thumb' => $this->generateExpectedThumbJson($thumbID),
			'parent_id' => $parentAlbumID,
		], $attrToMerge);
	}
}
