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
use Tests\TestCase;

class SharingWithNonAdminUserAndPublicSearchTest extends Base\SharingTestScenariosAbstract
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, false);
	}

	/**
	 * Ensures that the user does not the private photo and gets an
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
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson([
			'is_public' => false,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
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
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
				PublicAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson([
			'is_public' => false,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			],
		]);
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);
		$responseForUnsorted->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			],
		]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);
		$responseForRecent->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			],
		]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, 403);
	}

	/**
	 * Ensures that the user sees the public photo, but not the private one.
	 * Ensures that the user gets a `403 - Unauthenticated` for the album and
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
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => null],
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
				PublicAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, 403);
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
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => null],
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
				PublicAlbum::ID => ['thumb' => null],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)], // photo 1 is thumb, because starred photo are always picked first
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			],
		]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson([
			'is_public' => false,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID1]);
		$responseForUnsorted->assertJsonMissing(['title' => self::PHOTO_TRAIN_TITLE]);
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);
		$responseForUnsorted->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1), // despite that photo 1 is starred
			],
		]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			],
		]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['title' => self::PHOTO_MONGOLIA_TITLE]);

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
}
