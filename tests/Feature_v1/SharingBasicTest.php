<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v1;

use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BaseSharingTest;

class SharingBasicTest extends BaseSharingTest
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
		$albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add($albumID1, TestConstants::ALBUM_TITLE_2)->offsetGet('id');

		$response = $this->sharing_tests->list();
		$response->assertSimilarJson([
			'shared' => [],
			'albums' => [
				['id' => $albumID1, 'title' => TestConstants::ALBUM_TITLE_1],
				['id' => $albumID2, 'title' => TestConstants::ALBUM_TITLE_1 . '/' . TestConstants::ALBUM_TITLE_2],
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
		$albumID1 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_1)->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, TestConstants::ALBUM_TITLE_2)->offsetGet('id');
		$userID1 = $this->users_tests->add(TestConstants::USER_NAME_1, TestConstants::USER_PWD_1)->offsetGet('id');
		$userID2 = $this->users_tests->add(TestConstants::USER_NAME_2, TestConstants::USER_PWD_2)->offsetGet('id');

		$this->sharing_tests->add([$albumID1], [$userID1]);
		$response = $this->sharing_tests->list();

		$response->assertJson([
			'shared' => [[
				'user_id' => $userID1,
				'album_id' => $albumID1,
				'username' => TestConstants::USER_NAME_1,
				'title' => TestConstants::ALBUM_TITLE_1,
			]],
			'albums' => [
				['id' => $albumID1, 'title' => TestConstants::ALBUM_TITLE_1],
				['id' => $albumID2, 'title' => TestConstants::ALBUM_TITLE_2],
			],
		]);

		/** @var array $users */
		$users = $response->offsetGet('users');
		self::assertContains(['id' => $userID1, 'username' => TestConstants::USER_NAME_1], $users);
		self::assertContains(['id' => $userID2, 'username' => TestConstants::USER_NAME_2], $users);
		self::assertNotContains(['id' => 1, 'username' => 'admin'], $users);
	}
}