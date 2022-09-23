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
	public function testPhotosInSharedAndPrivateAlbum(): void
	{
		$this->preparePhotosInSharedAndPrivateAndRequireLinkAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$responseForRoot->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);
		$responseForRoot->assertJsonMissing(['id' => $this->albumID3]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID3]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->albumID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->albumID2]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['id' => $this->albumID3]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID3]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->albumID2]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);
		$responseForRecent->assertJsonMissing(['id' => $this->albumID3]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID3]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson());
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID2]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID3]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID3]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
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
			null, [
				$this->generateExpectedAlbumJson($this->albumID1, self::ALBUM_TITLE_1),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->albumID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson());
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), self::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testThreeAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		$this->prepareThreeAlbumsWithMixedSharingAndPasswordProtection();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			null, [
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2),
				$this->generateExpectedAlbumJson($this->albumID3, self::ALBUM_TITLE_3),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID3]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->albumID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->albumID2]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['id' => $this->albumID3]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID3]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->albumID2]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);
		$responseForRecent->assertJsonMissing(['id' => $this->albumID3]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID3]);

		// TODO: Should public and password-protected albums appear in tree? Regression?
		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson());
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID2]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID3]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID3]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->albums_tests->get($this->albumID2, $this->getExpectedInaccessibleHttpStatusCode(), self::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
		$this->albums_tests->get($this->albumID3, $this->getExpectedInaccessibleHttpStatusCode(), self::EXPECTED_PASSWORD_REQUIRED_MSG, $this->getExpectedDefaultInaccessibleMessage());
		$this->photos_tests->get($this->photoID3, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testThreeUnlockedAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		$this->prepareThreeAlbumsWithMixedSharingAndPasswordProtection();
		$this->albums_tests->unlock($this->albumID3, self::ALBUM_PWD_1);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null,
			null,
			null,
			$this->photoID3, [ // photo 3 is alphabetically first
				$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
				$this->generateExpectedAlbumJson($this->albumID3, self::ALBUM_TITLE_3, null, $this->photoID3),
			]
		));
		$responseForRoot->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->albumID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->albumID2]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['id' => $this->albumID3]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID3]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID3, [ // photo 3 is alphabetically first
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID3, $this->albumID3),
				$this->generateExpectedPhotoJson(self::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID2, $this->albumID2),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2),
			$this->generateExpectedAlbumJson($this->albumID3, self::ALBUM_TITLE_3, null, $this->photoID3),
		]));
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$responseForAlbum2 = $this->albums_tests->get($this->albumID2);
		$responseForAlbum2->assertJson($this->generateExpectedAlbumJson(
			$this->albumID2, self::ALBUM_TITLE_2, null, $this->photoID2
		));
		$this->photos_tests->get($this->photoID2);
		$responseForAlbum3 = $this->albums_tests->get($this->albumID3);
		$responseForAlbum3->assertJson($this->generateExpectedAlbumJson(
			$this->albumID3, self::ALBUM_TITLE_3, null, $this->photoID3
		));
		$this->photos_tests->get($this->photoID3);
	}

	protected function generateExpectedRootJson(
		?string $unsortedAlbumThumbID = null,
		?string $starredAlbumThumbID = null,
		?string $publicAlbumThumbID = null,
		?string $recentAlbumThumbID = null,
		array $expectedAlbumJson = []
	): array {
		if ($unsortedAlbumThumbID !== null) {
			throw new \InvalidArgumentException('$unsortedAlbumThumbID must be `null` for test with unauthenticated users');
		}
		if ($publicAlbumThumbID !== null) {
			throw new \InvalidArgumentException('$publicAlbumThumbID must be `null` for test with unauthenticated users');
		}

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

	protected function generateExpectedTreeJson(array $expectedAlbums = []): array
	{
		return [
			'albums' => $expectedAlbums,
			'shared_albums' => [],
		];
	}

	protected function performPostPreparatorySteps(): void
	{
		// This is a no-op for the anonymous user, because we do not need
		// to log in
	}

	protected function getExpectedInaccessibleHttpStatusCode(): int
	{
		return 401;
	}

	protected function getExpectedDefaultInaccessibleMessage(): string
	{
		return self::EXPECTED_UNAUTHENTICATED_MSG;
	}
}
