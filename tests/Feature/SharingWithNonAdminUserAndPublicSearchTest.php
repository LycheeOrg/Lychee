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
use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Tests\Feature\Base\BaseSharingWithNonAdminUser;
use Tests\Feature\Constants\TestConstants;

class SharingWithNonAdminUserAndPublicSearchTest extends BaseSharingWithNonAdminUser
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set(TestConstants::CONFIG_PUBLIC_HIDDEN, false);
	}

	/**
	 * Ensures that the user sees the unsorted public photos as the
	 * cover and inside "Recent", "On This Day" and "Favorites" (as public search is
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

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID1, $this->photoID1, $this->photoID1, $this->photoID1, $this->photoID1
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(
			$this->photoID1, $this->photoID1, $this->photoID1, $this->photoID1
		);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(
			false,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);
		$responseForUnsorted->assertJsonMissing(['title' => TestConstants::PHOTO_MONGOLIA_TITLE]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);
		$responseForRecent->assertJsonMissing(['title' => TestConstants::PHOTO_MONGOLIA_TITLE]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);
		$responseForStarred->assertJsonMissing(['title' => TestConstants::PHOTO_MONGOLIA_TITLE]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID2]);
		$responseForOnThisDay->assertJsonMissing(['title' => TestConstants::PHOTO_MONGOLIA_TITLE]);

		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
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

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1, $this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			null, $this->photoID1, $this->photoID1, $this->photoID1, $this->photoID1
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(
			null, $this->photoID1, $this->photoID1, $this->photoID1
		);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}
		$responseForRoot->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForRecent->assertJsonMissing(['id' => $this->photoID2]);

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForStarred->assertJsonMissing(['id' => $this->photoID2]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, $this->albumID1),
			]
		));
		$responseForOnThisDay->assertJsonMissing(['id' => $this->photoID2]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson());
		$responseForTree->assertJsonMissing(['id' => $this->albumID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID1]);
		$responseForTree->assertJsonMissing(['id' => $this->photoID2]);

		$this->albums_tests->get($this->albumID1, $this->getExpectedInaccessibleHttpStatusCode(), $this->getExpectedDefaultInaccessibleMessage(), TestConstants::EXPECTED_PASSWORD_REQUIRED_MSG);
		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2, $this->getExpectedInaccessibleHttpStatusCode());
	}

	public function testPublicUnsortedPhotoAndPhotoInSharedAlbum(): void
	{
		$this->preparePublicUnsortedPhotoAndPhotoInSharedAlbum();

		$this->ensurePhotosWereTakenOnThisDay($this->photoID1);
		$this->ensurePhotosWereNotTakenOnThisDay($this->photoID2);

		$responseForRoot = $this->root_album_tests->get();
		$responseForRoot->assertJson($this->generateExpectedRootJson(
			$this->photoID1,
			$this->photoID2,
			$this->photoID1,
			$this->photoID2,
			$this->photoID1, [
				$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID2),
			]
		));
		$arrayUnexpected = $this->generateUnexpectedRootJson(
			$this->photoID1, $this->photoID2, $this->photoID1, $this->photoID2
		);
		if ($arrayUnexpected !== null) {
			$responseForRoot->assertJsonMissing($arrayUnexpected);
		}

		$responseForUnsorted = $this->albums_tests->get(UnsortedAlbum::ID);
		$responseForUnsorted->assertJson($this->generateExpectedSmartAlbumJson(
			false,
			$this->photoID1, [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));
		$responseForUnsorted->assertJsonMissing(['id' => $this->photoID2]);

		$responseForRecent = $this->albums_tests->get(RecentAlbum::ID);
		$responseForRecent->assertJson($this->generateExpectedSmartAlbumJson(
			true,
			$this->photoID2, [ // photo 2 is thumb, because both photos are starred, but photo 2 is sorted first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			]
		));

		$responseForStarred = $this->albums_tests->get(StarredAlbum::ID);
		$responseForStarred->assertJson([
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1), // photo 2 is alphabetically first
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			],
		]);

		$responseForOnThisDay = $this->albums_tests->get(OnThisDayAlbum::ID);
		$responseForOnThisDay->assertJson([
			'policy' => ['is_public' => true],
			'thumb' => $this->generateExpectedThumbJson($this->photoID1),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_TRAIN_IMAGE, $this->photoID1, null),
			],
		]);

		$responseForTree = $this->root_album_tests->getTree();
		$responseForTree->assertJson($this->generateExpectedTreeJson([
			$this->generateExpectedAlbumJson($this->albumID1, TestConstants::ALBUM_TITLE_1, null, $this->photoID2),
		]));

		$responseForAlbum = $this->albums_tests->get($this->albumID1);
		$responseForAlbum->assertJson([
			'id' => $this->albumID1,
			'title' => TestConstants::ALBUM_TITLE_1,
			'policy' => ['is_public' => false],
			'thumb' => $this->generateExpectedThumbJson($this->photoID2),
			'photos' => [
				$this->generateExpectedPhotoJson(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE, $this->photoID2, $this->albumID1),
			],
		]);

		$this->photos_tests->get($this->photoID1);
		$this->photos_tests->get($this->photoID2);
	}
}
