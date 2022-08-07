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

use Tests\Feature\Base\SharingTestBase;

class SharingTest extends SharingTestBase
{
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
}
