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
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;

/**
 * Implements the tests of {@link SharingTestScenariosAbstract} for a
 * non-admin user whose results are independent of the setting for public
 * search.
 */
abstract class SharingWithNonAdminUserAbstract extends SharingTestScenariosAbstract
{
	/**
	 * {@inheritDoc}
	 */
	public function testUnsortedPrivatePhoto(): void
	{
		parent::testUnsortedPrivatePhoto();

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID1]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function testTwoPhotosInPublicAlbum(): void
	{
		parent::testTwoPhotosInPublicAlbum();

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(false));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID1]);
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);
	}

	public function testPhotosInSharedAndPrivateAlbum(): void
	{
		$this->preparePhotosInSharedAndPrivateAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1, [
				self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->albumID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->albumID2]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
		]));
		$responseForTree->assertJsonMissing(['id' => $this->albumID2]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);

		$responseForAlbum1 = $this->albums_tests->get($this->albumID1);
		$responseForAlbum1->assertJson(self::generateExpectedAlbumJson(
			$this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1
		));
		$this->photos_tests->get($this->photoID1);

		$this->albums_tests->get($this->albumID2, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testPhotoInSharedPublicPasswordProtectedAlbum(): void
	{
		$this->preparePhotoInSharedPublicPasswordProtectedAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID1, [
				self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->albumID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID1, $this->albumID1),
			]
		));

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			self::generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1),
		]));

		$responseForAlbum = $this->albums_tests->get($this->albumID1);
		$responseForAlbum->assertJson($this->generateExpectedAlbumJson(
			$this->albumID1, self::ALBUM_TITLE_1, null, $this->photoID1
		));
		$this->photos_tests->get($this->photoID1);
	}

	protected function generateExpectedRootJson(
		?string $unsortedAlbumThumbID = null,
		?string $starredAlbumThumbID = null,
		?string $publicAlbumThumbID = null,
		?string $recentAlbumThumbID = null,
		array $expectedAlbumJson = []
	): array {
		return [
			'smart_albums' => [
				UnsortedAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($unsortedAlbumThumbID)],
				StarredAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($starredAlbumThumbID)],
				PublicAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($publicAlbumThumbID)],
				RecentAlbum::ID => ['thumb' => $this->generateExpectedThumbJson($recentAlbumThumbID)],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => $expectedAlbumJson,
		];
	}

	protected function generateExpectedTreeJson(array $expectedAlbums = []): array
	{
		return [
			'albums' => [],
			'shared_albums' => $expectedAlbums,
		];
	}

	protected function performPostPreparatorySteps(): void
	{
		AccessControl::log_as_id($this->userID);
	}

	protected function getExpectedInaccessibleHttpStatusCode(): int
	{
		return 403;
	}

	protected function getExpectedDefaultInaccessibleMessage(): string
	{
		return self::EXPECTED_FORBIDDEN_MSG;
	}
}
