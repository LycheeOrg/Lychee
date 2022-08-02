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

use App\Models\Configs;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Tests\Feature\Base\SharingTestScenariosAbstract;
use Tests\TestCase;

class SharingWithAnonUserAndNoPublicSearchTest extends Base\SharingTestScenariosAbstract
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set(TestCase::CONFIG_PUBLIC_HIDDEN, true);
	}

	/**
	 * Ensures that the user does not the private photo and gets an
	 * "Unauthenticated" response.
	 *
	 * See {@link SharingTestScenariosAbstract::prepareUnsortedPublicAndPrivatePhoto()}
	 * for description of scenario.
	 *
	 * @return void
	 */
	public function testUnsortedPrivatePhoto(): void
	{
		$this->prepareUnsortedPrivatePhoto();

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
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);

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

		$this->photos_tests->get($this->photoID1, 401);
	}

	/**
	 * Ensures that the user does not the unsorted public photos as covers nor
	 * inside "Recent" and "Favorites" (as public search is disabled).
	 * The user can access the public photo nonetheless, but gets
	 * "401 - Unauthenticated" for the other.
	 *
	 * See
	 * {@link SharingTestScenariosAbstract::prepareUnsortedPublicAndPrivatePhoto()}
	 * for description of scenario.
	 *
	 * @return void
	 */
	public function testUnsortedPublicAndPrivatePhoto(): void
	{
		$this->prepareUnsortedPublicAndPrivatePhoto();

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
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);


		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		// Even though the public photo is not searchable and hence does not
		// show up in the smart albums, it can be fetched directly
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, 401);
	}

	/**
	 * Ensures that the user does not see any photo, although the first is
	 * public (but not searchable).
	 * The first photo is still visible if directly accessed, but the user
	 * gets a `401 - Unauthenticated` for the album and the second photo.
	 *
	 * See
	 * {@link SharingTestScenariosAbstract::preparePublicAndPrivatePhotoInPrivateAlbum()}
	 * for description of scenario.
	 */
	public function testPublicAndPrivatePhotoInPrivateAlbum(): void
	{
		$this->preparePublicAndPrivatePhotoInPrivateAlbum();

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
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson([
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'is_public' => true,
			'thumb' => null,
			'photos' => [],
		]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		// The album and photo 2 are not accessible, but photo 1 is
		// because it is public even though it is contained in an inaccessible
		// album
		$this->albums_tests->get($this->albumID1, 401);
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, 401);
	}

	/**
	 * See
	 * {@link SharingWithAnonUserAndNoPublicSearchTest::testUnsortedPublicAndPrivatePhoto()}
	 * and
	 * {@link SharingWithAnonUserAndNoPublicSearchTest::testPublicAndPrivatePhotoInPrivateAlbum()}.
	 *
	 * In comparison both photos are visible, because the album is public,
	 * although public search is disabled, because
	 * the photo is inside a public album which is browsable.
	 *
	 * Note that the setting "public search" only affects photos which are
	 * made public explicitly.
	 */
	public function testTwoPhotosInPublicAlbum(): void
	{
		$this->prepareTwoPhotosInPublicAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson([
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($this->photoID1)], // photo 1 is thumb, because starred photo are always picked first
			],
			'tag_albums' => [],
			'albums' => [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			],
			'shared_albums' => [],
		]);

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
