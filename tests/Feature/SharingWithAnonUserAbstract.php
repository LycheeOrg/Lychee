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

use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;

/**
 * Implements the tests of {@link SharingTestScenariosAbstract} for an
 * anonymous user whose results are independent of the setting for public
 * search.
 */
abstract class SharingWithAnonUserAbstract extends SharingTestScenariosAbstract
{
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
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson());
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson());
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);

		$this->photos_tests->get($this->photoID1, 401);
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
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID1,
			$this->photoID1, [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1), // photo 1 is thumb, because starred photo are always picked first
			])
		);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID1, [ // photo 1 is the thumb, because starred photo are always picked first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1), // despite that photo 1 is starred
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID1, [
				$this->generateExpectedPhotoJson(static::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1), // photo 1 is thumb, because starred photo are always picked first
		]));
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

	public function testPublicAlbumAndPasswordProtectedAlbum(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID2, [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1), // album 1 is in password protected, still locked album
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]); // photo 1 is in password protected, still locked album

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID2, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson());
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, 401, self::EXPECTED_PASSWORD_REQUIRED_MSG, self::EXPECTED_UNAUTHENTICATED_MSG);
		$this->photos_tests->get($this->photoID1, 401);

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
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
		]));
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
	}

	public function testPublicAlbumAndPasswordProtectedUnlockedAlbum(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbum();
		$this->albums_tests->unlock($this->albumID1, self::ALBUM_PWD_1);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1, [  // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson());
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
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
		]));
	}

	public function testPublicAlbumAndPasswordProtectedAlbumWithStarredPhoto(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbumWithStarredPhoto();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID2, [  // album 1 is password protected, hence photo 2 is the thumb
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1), // album 1 is in password protected, still locked album
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]); // photo 1 is in password protected, still locked album

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID2, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson());
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, 401, self::EXPECTED_PASSWORD_REQUIRED_MSG, self::EXPECTED_UNAUTHENTICATED_MSG);
		$this->photos_tests->get($this->photoID1, 401);

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
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
		]));
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
	}

	public function testPublicAlbumAndPasswordProtectedUnlockedAlbumWithStarredPhoto(): void
	{
		$this->preparePublicAlbumAndPasswordProtectedAlbumWithStarredPhoto();
		$this->albums_tests->unlock($this->albumID1, self::ALBUM_PWD_1);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID1,  // album 1 is unlocked, and photo 1 is alphabetically first
			$this->photoID1, [  // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			]
		));

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID1, [ // album 1 is unlocked, and photo 1 is alphabetically first
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
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
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			self::generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
		]));
	}

	public function testPublicAlbumAndHiddenAlbum(): void
	{
		$this->preparePublicAlbumAndHiddenAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID1, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson());
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
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
		]));
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}

	public function testPublicAlbumAndHiddenPasswordProtectedAlbum(): void
	{
		$this->preparePublicAlbumAndHiddenPasswordProtectedAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID1, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson());
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

		$this->albums_tests->get($this->albumID2, 401, self::EXPECTED_PASSWORD_REQUIRED_MSG, self::EXPECTED_UNAUTHENTICATED_MSG);
		$this->photos_tests->get($this->photoID2, 401, self::EXPECTED_UNAUTHENTICATED_MSG);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
		]));
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}

	public function testPublicAlbumAndHiddenPasswordProtectedUnlockedAlbum(): void
	{
		$this->preparePublicAlbumAndHiddenPasswordProtectedAlbum();
		$this->albums_tests->unlock($this->albumID2, self::ALBUM_PWD_2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			$this->photoID1, [ // album 2 is hidden
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]); // album 2 is hidden
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]); // album 2 is hidden

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			$this->photoID1, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson());
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
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
		]));
		$responseForTree->assertDontSee(['id' => $this->albumID2]);
	}

	protected function generateExpectedRootJson(
		?string $starredAlbumThumbID = null,
		?string $recentAlbumThumbID = null,
		array $expectedAlbumJson = []
	): array {
		return [
			'smart_albums' => [
				UnsortedAlbum::ID => null,
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($starredAlbumThumbID)],
				PublicAlbum::ID => null,
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recentAlbumThumbID)],
			],
			'tag_albums' => [],
			'albums' => $expectedAlbumJson,
			'shared_albums' => [],
		];
	}

	protected function generateExpectedSmartAlbumJson(
		?string $thumbID = null,
		array $expectedPhotos = []
	): array {
		return [
			'is_public' => true,
			'thumb' => $this->generateExpectedThumbJson($thumbID),
			'photos' => $expectedPhotos,
		];
	}

	protected function generateExpectedTreeJson(array $expectedAlbums = []): array
	{
		return [
			'albums' => $expectedAlbums,
			'shared_albums' => [],
		];
	}
}
