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

class SharingBasicTest extends Base\BaseSharingTest
{
	/**
	 * @return void
	 */
	public function testEmptySharingList(): void
	{
		$response = $this->sharing_tests->list();
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
		$albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add($albumID1, self::ALBUM_TITLE_2)->offsetGet('id');

		$response = $this->sharing_tests->list();
		$response->assertSimilarJson([
			'shared' => [],
			'albums' => [
				['id' => $albumID1, 'title' => self::ALBUM_TITLE_1],
				['id' => $albumID2, 'title' => self::ALBUM_TITLE_1 . '/' . self::ALBUM_TITLE_2],
			],
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
		$albumID1 = $this->albums_tests->add(null, self::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, self::ALBUM_TITLE_2)->offsetGet('id');
		$userID1 = $this->users_tests->add(self::USER_NAME_1, self::USER_PWD_1)->offsetGet('id');
		$userID2 = $this->users_tests->add(self::USER_NAME_2, self::USER_PWD_2)->offsetGet('id');

		$this->sharing_tests->add([$albumID1], [$userID1]);
		$response = $this->sharing_tests->list();

		$response->assertJson([
			'shared' => [[
				'user_id' => $userID1,
				'album_id' => $albumID1,
				'username' => self::USER_NAME_1,
				'title' => self::ALBUM_TITLE_1,
			]],
			'albums' => [
				['id' => $albumID1, 'title' => self::ALBUM_TITLE_1],
				['id' => $albumID2, 'title' => self::ALBUM_TITLE_2],
			],
			'users' => [
				['id' => $userID1, 'username' => self::USER_NAME_1],
				['id' => $userID2, 'username' => self::USER_NAME_2],
			],
		]);
	}
}