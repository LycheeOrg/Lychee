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

use Tests\Feature\Base\PhotoTestBase;
use Tests\Feature\Lib\SharingUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyUsers;

class SharingTest extends PhotoTestBase
{
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;

	protected SharingUnitTest $sharing_test;
	protected UsersUnitTest $users_test;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyUsers();
		$this->sharing_test = new SharingUnitTest($this);
		$this->users_test = new UsersUnitTest($this);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testEmptySharingList(): void
	{
		$response = $this->sharing_test->list();
		$response->assertExactJson([
			'shared' => [],
			'albums' => [],
			'users' => [],
		]);
	}

	/**
	 * @return void
	 */
	public function testSharingListWithAlbums(): void
	{
		$albumID1 = $this->albums_tests->add(null, 'test_album')->offsetGet('id');
		$albumID2 = $this->albums_tests->add($albumID1, 'test_album2')->offsetGet('id');

		$response = $this->sharing_test->list();
		$response->assertSimilarJson([
			'shared' => [],
			'albums' => [[
				'id' => $albumID1,
				'title' => 'test_album',
			], [
				'id' => $albumID2,
				'title' => 'test_album/test_album2',
			]],
			'users' => [],
		]);
	}

	/**
	 * Adds albums and users, shares album with users and asserts that
	 * sharing list is correct.
	 *
	 * @return void
	 */
	public function testSharingListWithSharedAlbums(): void
	{
		$albumID1 = $this->albums_tests->add(null, 'test_album_1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'test_album_2')->offsetGet('id');
		$userID1 = $this->users_test->add('test_user_1', 'test_password_1')->offsetGet('id');
		$userID2 = $this->users_test->add('test_user_2', 'test_password_2')->offsetGet('id');

		$this->sharing_test->add([$albumID1], [$userID1]);
		$response = $this->sharing_test->list();

		$response->assertJson([
			'shared' => [[
				'user_id' => $userID1,
				'album_id' => $albumID1,
				'username' => 'test_user_1',
				'title' => 'test_album_1',
			]],
			'albums' => [[
				'id' => $albumID1,
				'title' => 'test_album_1',
			], [
				'id' => $albumID2,
				'title' => 'test_album_2',
			]],
			'users' => [[
				'id' => $userID1,
				'username' => 'test_user_1',
			], [
				'id' => $userID2,
				'username' => 'test_user_2',
			]],
		]);
	}

	/**
	 * Creates two albums, puts a single photo in each, shares one
	 * album with a user, logs in as the user and checks that the user
	 * has proper access rights.
	 *
	 * In particular the following checks are made:
	 *  - the user sees the album (incl. its cover) but not the other album
	 *    nor image
	 *  - the user sees the image of the public album in "Recent"
	 *  - the album tree contains the one album (incl. the photo) but not
	 *    the other one
	 *  - the user cannot access the non-shared album
	 *  - the user cannot access the non-shared photo
	 *
	 * @return void
	 */
	public function testAlbumSharedWithUser(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Uploads a photo, logs in as another user, checks that the user
	 * does not see the photo.
	 *
	 * In particular the following checks are made:
	 *  - the user does not see the photo in "Recent"
	 *  - the user cannot access the photo
	 *
	 * @return void
	 */
	public function testUnsortedPrivatePhoto(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Uploads two photos, marks the least recent photo as public and stars it,
	 * logs out, checks that the anonymous user does see the photo, logs in
	 * as another user, checks again.
	 *
	 * In particular the following checks are made:
	 *  - the (anonymous) user sees the public photo as the cover of
	 *    "Recent" and "Favorites" (but not the other one which is actually
	 *    more recent)
	 *  - the (anonymous) user sees the public photo in "Recent" and
	 *    "Favorites" but not the other one
	 *
	 * @return void
	 */
	public function testUnsortedPublicPhoto(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * See {@link SharingTest::testUnsortedPublicPhoto()} but for photos
	 * in an album.
	 *
	 * @return void
	 */
	public function testPublicPhoto(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Uploads an unsorted photo and another photo into an album,
	 * marks the unsorted photo as public, shares the album with another user,
	 * logs out, check that the anonymous user only sees the public photo,
	 * logs in as the other user and checks that both photos are visible.
	 *
	 * In particular the following checks are made:
	 *  - the anonymous user only sees the public photo in "Recent"
	 *    (inside and as a cover)
	 *  - the other user sees both photos in "Recent" and the shared photo
	 *    as cover (because it is more recent)
	 *
	 * @return void
	 */
	public function testPublicPhotoAndSharedAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Uploads two photos into two albums (one photo per album), marks one
	 * album as public and the other one as password-protected,
	 * logs out, checks that the anonymous user only sees both albums,
	 * but only the cover of the public one, provide password, checks that
	 * now both covers are visible.
	 *
	 * In particular the following checks are made:
	 *  - before the password has been provided the anonymous user only sees
	 *    the public photo
	 *     - as a cover of the public album
	 *     - in "Recent"
	 *     - in the album tree
	 *  - after the password has been provided the anonymous user sees both
	 *    photos
	 *     - as covers
	 *     - in "Recent"
	 *     - in the album tree
	 *
	 * @return void
	 */
	public function testPublicAlbumAndPasswordProtectedAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Like {@link SharingTest::testPublicAlbumAndPasswordProtectedAlbum},
	 * but additionally the password-protected photo is starred and the
	 * "Favorites" album is tested as well.
	 *
	 * @return void
	 */
	public function testPublicAlbumAndPasswordProtectedAlbumWithStarredPhoto(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Uploads two photos into two albums (one photo per album), marks one
	 * album as public and the other one as public and hidden,
	 * logs out, checks that the anonymous user only see the first album,
	 * accesses the second album and checks again that the anonymous user
	 * still only sees the first album.
	 *
	 * In particular the following checks are made:
	 *  - before and after the hidden album has been accessed, the anonymous
	 *    user only sees the public, not hidden photo
	 *     - as a cover of the public album
	 *     - in "Recent"
	 *     - in the album tree
	 *
	 * @return void
	 */
	public function testPublicAlbumAndHiddenAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
	}

	/**
	 * Like {@link SharingTest::testPublicAlbumAndHiddenAlbum}, but
	 * additionally the hidden album is also password protected.
	 *
	 * @return void
	 */
	public function testPublicAlbumAndHiddenPasswordProtectedAlbum(): void
	{
		static::markTestIncomplete('Not written yet');
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
	 * a user, mark the second and third as public and password protected,
	 * login as user, check that album 1 and 2 are visible, unlock album 3,
	 * check that all albums are visible.
	 *
	 * In particular, each visibility check includes
	 *  - the content inside the album itself
	 *  - the album "Recent"
	 *  - the album tree
	 *
	 * This test asserts that a unlocking an album does not badly interfere
	 * with another album which is shared but also happens to be protected
	 * by the same password.
	 *
	 * @return void
	 */
	public function testThreeAlbumsWithMixedSharingAndPasswordProtection(): void
	{
		static::markTestIncomplete('Not written yet');
	}
}
