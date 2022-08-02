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
use Tests\Feature\Base\SharingTestBase;

class SharingAdvancedTest extends SharingTestBase
{
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
	public function testAlbumSharedNonAdminWithUser(): void
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

}