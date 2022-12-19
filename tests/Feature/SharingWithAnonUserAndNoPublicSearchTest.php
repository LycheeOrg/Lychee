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
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use Tests\AbstractTestCase;
use Tests\Feature\Base\BaseSharingWithAnonUser;

class SharingWithAnonUserAndNoPublicSearchTest extends BaseSharingWithAnonUser
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set(AbstractTestCase::CONFIG_PUBLIC_HIDDEN, true);
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
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		// Even though the public photo is not searchable and hence does not
		// show up in the smart albums, it can be fetched directly
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
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
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson());
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);

		// The album and photo 2 are not accessible, but photo 1 is
		// because it is public even though it is contained in an inaccessible
		// album
		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testPublicUnsortedPhotoAndPhotoInSharedAlbum(): void
	{
		$this->preparePublicUnsortedPhotoAndPhotoInSharedAlbum();

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson());
		$responseForRoot->assertJsonMissing(['id' => $this->albumID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID1]);
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(true));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID1]);
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson());
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), self::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}
}
