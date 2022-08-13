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
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Tests\TestCase;

class SharingWithNonAdminUserAndPublicSearchTest extends Base\SharingWithNonAdminUserAbstract
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, false);
	}

	/**
	 * Ensures that the user does not the private photo and gets a
	 * "Forbidden" response.
	 *
	 * See {@link SharingTestScenariosAbstract::prepareUnsortedPublicAndPrivatePhoto()}
	 * for description of scenario.
	 *
	 * @return void
	 */
	public function testUnsortedPrivatePhoto(): void
	{
		$this->prepareUnsortedPrivatePhoto();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);

		$this->photos_tests->get($this->photoID1, 403);
	}

	/**
	 * Ensures that the user sees the unsorted public photos as the
	 * cover and inside "Recent" and "Favorites" (as public search is
	 * enabled), but not the other photo.
	 * The user can access the public photo, but gets
	 * "403 - Forbidden" for the other.
	 *
	 * See {@link SharingTestScenariosAbstract::prepareUnsortedPublicAndPrivatePhoto()}
	 * for description of scenario.
	 *
	 * @return void
	 */
	public function testUnsortedPublicAndPrivatePhoto(): void
	{
		$this->prepareUnsortedPublicAndPrivatePhoto();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID1, $this->photoID1, $this->photoID1, $this->photoID1
		));
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(
			false,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);
		$responseForUnsorted->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, 403);
	}

	/**
	 * Ensures that the user sees the public photo, but not the private one.
	 * Ensures that the user gets a `403 - Forbidden` for the album and
	 * the second photo.
	 *
	 * See
	 * {@link SharingTestScenariosAbstract::preparePublicAndPrivatePhotoInPrivateAlbum()}
	 * for description of scenario.
	 */
	public function testPublicAndPrivatePhotoInPrivateAlbum(): void
	{
		$this->preparePublicAndPrivatePhotoInPrivateAlbum();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null, $this->photoID1, $this->photoID1, $this->photoID1
		));
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, 403, self::EXPECTED_FORBIDDEN_MSG, self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, 403);
	}

	/**
	 * See
	 * {@link SharingWithNonAdminUserAndPublicSearchTest::testUnsortedPublicAndPrivatePhoto()}
	 * and
	 * {@link SharingWithNonAdminUserAndPublicSearchTest::testPublicAndPrivatePhotoInPrivateAlbum()}.
	 *
	 * In comparison both photos are visible, because the album is public,
	 * although public search is disabled, because
	 * the photo is inside a public album which is browsable.
	 *
	 * Note that the setting "public search" only affects photos which are
	 * made public explicitly.
	 *
	 * Also, the public album does appear in `shared_albums` (and not in
	 * `albums`) as the non-admin user is authenticated and thus may see the
	 * ownership of the album.
	 */
	public function testTwoPhotosInPublicAlbum(): void
	{
		$this->prepareTwoPhotosInPublicAlbum();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1,
			null,
			$this->photoID1, [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			])
		);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID1]);
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // photo 1 is the thumb, because starred photo are always picked first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1), // despite that photo 1 is starred
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			],
		]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum = $this->albums_tests->get($this->albumID1);
		$responseForAlbum->assertJson([
			'id' => $this->albumID1,
			'title' => self::ALBUM_TITLE_1,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1), // despite that photo 1 is starred
			],
		]);
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2);
	}

	public function testPublicUnsortedPhotoAndPhotoInSharedAlbum(): void
	{
		$this->preparePublicUnsortedPhotoAndPhotoInSharedAlbum();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID1,
			$this->photoID2,
			$this->photoID1,
			$this->photoID2, [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID2),
			]
		));

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(
			false,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [ // photo 2 is thumb, because both photos are starred, but photo 2 is sorted first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			],
		]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID2),
			],
		]);

		$responseForAlbum = $this->albums_tests->get($this->albumID1);
		$responseForAlbum->assertJson([
			'id' => $this->albumID1,
			'title' => self::ALBUM_TITLE_1,
			'is_public' => false,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1),
			],
		]);

		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2);
	}

	public function testPublicAlbumAndPasswordProtectedAlbum(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbum();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID2, [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1), // album 1 is in password protected, still locked album
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]); // photo 1 is in password protected, still locked album

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, 403, self::EXPECTED_PASSWORD_REQUIRED_MSG, self::EXPECTED_FORBIDDEN_MSG);
		$this->photos_tests->get($this->photoID1, 403);
		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => self::ALBUM_TITLE_2,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			],
		]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);

		$this->albums_tests->unlock($this->albumID1, self::ALBUM_PWD_1);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1, [  // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => self::ALBUM_TITLE_1,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);
		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => self::ALBUM_TITLE_2,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
				self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			],
		]);
	}

	public function testPublicAlbumAndPasswordProtectedAlbumWithStarredPhoto()
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbumWithStarredPhoto();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID2, [  // album 1 is password protected, hence photo 2 is the thumb
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1), // album 1 is in password protected, still locked album
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]); // photo 1 is in password protected, still locked album

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, 403, self::EXPECTED_PASSWORD_REQUIRED_MSG, self::EXPECTED_FORBIDDEN_MSG);
		$this->photos_tests->get($this->photoID1, 403);
		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => self::ALBUM_TITLE_2,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			],
		]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);

		$this->albums_tests->unlock($this->albumID1, self::ALBUM_PWD_1);
		$this->clearCachedSmartAlbums();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1,  // album 1 is unlocked, and photo 1 is alphabetically first
			null,
			$this->photoID1, [  // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => self::ALBUM_TITLE_1,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);
		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => self::ALBUM_TITLE_2,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
				self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			],
		]);
	}

	public function testPublicAlbumAndHiddenAlbum()
	{
		$this->preparePublicAlbumAndHiddenAlbum();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => self::ALBUM_TITLE_1,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => self::ALBUM_TITLE_2,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			],
		]);
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}

	public function testPublicAlbumAndHiddenPasswordProtectedAlbum()
	{
		$this->preparePublicAlbumAndHiddenPasswordProtectedAlbum();
		AccessControl::log_as_id($this->userID);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson([
			'id' => $this->albumID1,
			'title' => self::ALBUM_TITLE_1,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$this->photos_tests->get($this->photoID1);

		$this->albums_tests->get($this->albumID2, 403, self::EXPECTED_PASSWORD_REQUIRED_MSG, self::EXPECTED_FORBIDDEN_MSG);
		$this->photos_tests->get($this->photoID2, 403, self::EXPECTED_FORBIDDEN_MSG);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			],
		]);
		$responseForTree->assertDontSee(['id' => $this->albumID2]);

		$this->albums_tests->unlock($this->albumID2, self::ALBUM_PWD_2);
		$this->clearCachedSmartAlbums();

		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson([
			'id' => $this->albumID2,
			'title' => self::ALBUM_TITLE_2,
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			],
		]);
		$this->photos_tests->get($this->photoID2);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson([
			'albums' => [],
			'shared_albums' => [
				self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			],
		]);
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}
}
