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
		$this->preparePhotosInSharedAndPrivateAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$responseForRoot->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->albumID2]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->albumID2]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson());
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
		$responseForTree->assertJsonMissing(['id' => $this->albumID2]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1, $this->getExpectedInaccessibleHttpStatusCode());
		$this->albums_tests->get($this->albumID2, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
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
