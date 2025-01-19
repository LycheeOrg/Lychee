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

use App\Enum\DefaultAlbumProtectionType;
use App\Models\Configs;
use App\SmartAlbums\OnThisDayAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\LibUnitTests\AlbumsUnitTest;
use Tests\Feature_v1\LibUnitTests\PhotosUnitTest;
use Tests\Feature_v1\LibUnitTests\RootAlbumUnitTest;
use Tests\Feature_v1\LibUnitTests\SharingUnitTest;
use Tests\Feature_v1\LibUnitTests\UsersUnitTest;
use Tests\Traits\InteractWithSmartAlbums;
use Tests\Traits\RequiresEmptyAlbums;
use Tests\Traits\RequiresEmptyPhotos;
use Tests\Traits\RequiresEmptyUsers;

class AlbumTest extends AbstractTestCase
{
	use InteractWithSmartAlbums;
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;
	use RequiresEmptyPhotos;

	public const ENABLE_UNSORTED = 'enable_unsorted';
	public const ENABLE_STARRED = 'enable_starred';
	public const ENABLE_RECENT = 'enable_recent';
	public const ENABLE_ON_THIS_DAY = 'enable_on_this_day';

	protected AlbumsUnitTest $albums_tests;
	protected RootAlbumUnitTest $root_album_tests;
	protected UsersUnitTest $users_tests;
	protected SharingUnitTest $sharing_tests;
	protected PhotosUnitTest $photos_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->setUpRequiresEmptyPhotos();
		$this->albums_tests = new AlbumsUnitTest($this);
		$this->root_album_tests = new RootAlbumUnitTest($this);
		$this->users_tests = new UsersUnitTest($this);
		$this->sharing_tests = new SharingUnitTest($this);
		$this->photos_tests = new PhotosUnitTest($this);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresEmptyAlbums();
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	/**
	 * Test album functions.
	 *
	 * @return void
	 */
	public function testAddNotLogged(): void
	{
		$this->clearCachedSmartAlbums();
		$this->albums_tests->add(null, 'test_album', 401);

		$this->albums_tests->get(RecentAlbum::ID, 401);
		$this->albums_tests->get(StarredAlbum::ID, 401);
		$this->albums_tests->get(UnsortedAlbum::ID, 401);
		$this->albums_tests->get(OnThisDayAlbum::ID, 401);

		// Ensure that we get proper 404 (not found) response for a
		// non-existing album, not a false 403 (forbidden) response
		$this->albums_tests->get('abcdefghijklmnopqrstuvwx', 404);
	}

	public function testAddReadLogged(): void
	{
		Auth::loginUsingId(1);
		$this->clearCachedSmartAlbums();

		$this->albums_tests->get(RecentAlbum::ID);
		$this->albums_tests->get(StarredAlbum::ID);
		$this->albums_tests->get(UnsortedAlbum::ID);
		$this->albums_tests->get(OnThisDayAlbum::ID);

		$albumID1 = $this->albums_tests->add(null, 'test_album')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'test_album2')->offsetGet('id');
		$albumID3 = $this->albums_tests->add(null, 'test_album3')->offsetGet('id');
		$albumTagID1 = $this->albums_tests->addByTags('test_tag_album1', ['test'])->offsetGet('id');

		$this->albums_tests->set_tags($albumTagID1, ['test', 'cool_new_tag', 'second_new_tag']);
		$response = $this->albums_tests->get($albumTagID1);
		$response->assertJson([
			'show_tags' => ['test', 'cool_new_tag', 'second_new_tag'],
		]);

		$this->root_album_tests->get(200, $albumID1);
		$this->root_album_tests->get(200, $albumID2);
		$this->root_album_tests->get(200, $albumID3);
		$this->root_album_tests->get(200, $albumTagID1);

		$this->albums_tests->move([$albumTagID1], $albumID3, 404);
		$this->albums_tests->move([$albumID3], $albumID2);
		$this->albums_tests->move([$albumID2], $albumID1);
		$this->albums_tests->move([$albumID3], null);

		/*
		 * try to get a non-existing album
		 */
		$this->albums_tests->get('abcdefghijklmnopqrstuvwx', 404);

		$response = $this->albums_tests->get($albumID1);
		$response->assertJson([
			'id' => $albumID1,
			'description' => null,
			'title' => 'test_album',
			'albums' => [['id' => $albumID2]],
			'num_subalbums' => 1,
		]);

		$this->albums_tests->set_title($albumID1, 'NEW_TEST');
		$this->albums_tests->set_description($albumID1, 'new description');
		$this->albums_tests->set_license($albumID1, 'WTFPL', 422);
		$this->albums_tests->set_license($albumID1, 'reserved');
		$this->albums_tests->set_sorting($albumID1, 'title', 'ASC');

		/**
		 * Let's see if the info changed.
		 */
		$response = $this->albums_tests->get($albumID1);
		$response->assertJson([
			'id' => $albumID1,
			'description' => 'new description',
			'title' => 'NEW_TEST',
		]);

		$this->albums_tests->set_sorting($albumID1, '', 'ASC');

		/*
		 * Flush the session to see if we can access the album
		 */
		Auth::logout();
		Session::flush();

		/*
		 * Let's try to get the info of the album we just created.
		 */
		$this->albums_tests->unlock($albumID1, '', 422);
		$this->albums_tests->unlock($albumID1, 'wrong-password', 403);
		$this->albums_tests->get($albumID1, 401);

		Auth::loginUsingId(1);

		/*
		 * Let's try to delete this album.
		 */
		$this->albums_tests->delete([$albumID1]);
		$this->albums_tests->delete([$albumID3]);
		$this->albums_tests->delete([$albumTagID1]);

		/*
		 * Because we deleted the album, we should not see it anymore.
		 */
		$this->root_album_tests->get(200, null, $albumID1);
		$this->root_album_tests->get(200, null, $albumID3);
		$this->root_album_tests->get(200, null, $albumTagID1);

		Auth::logout();
		Session::flush();
	}

	/**
	 * Tests that the nested-set model remains consistent for a multi-delete of a forest.
	 *
	 * This tests considers the following album layout (the `_lft`, `_rgt`
	 * indices of the nested set model are illustrated):
	 *
	 *                             (root)
	 *                               |
	 *                            Album 1
	 *                            ( 1,14)
	 *                               |
	 *            +------------------+------------------+
	 *            |                  |                  |
	 *      Sub-Album 1.1      Sub-Album 1.2      Sub-Album 1.3
	 *          (2,5)              (6,9)             (10,13)
	 *            |                  |                  |
	 *     Sub-Album 1.1.1    Sub-Album 1.2.1    Sub-Album 1.3.1
	 *          (3,4)              (7,8)             (11,12)
	 *
	 * We then do a _simultaneous_ multi-delete for album 1.1 and 1.3
	 * and expect the nested-set model to be updated like
	 *
	 *                             (root)
	 *                               |
	 *                            Album 1
	 *                             (1,6)
	 *                               |
	 *                         Sub-Album 1.2
	 *                             (2,5)
	 *                               |
	 *                        Sub-Album 1.2.1
	 *                             (3,4)
	 *
	 * @return void
	 */
	public function testMultiDelete(): void
	{
		try {
			// In order check the (_lft,_rgt)-indices we need deterministic
			// indices.
			// Hence, we must ensure that there are no left-overs from previous
			// tests.
			static::assertDatabaseCount('base_albums', 0);

			Auth::loginUsingId(1);

			// Create the test layout
			$albumID1 = $this->albums_tests->add(null, 'Album 1')->offsetGet('id');
			$albumID11 = $this->albums_tests->add($albumID1, 'Album 1.1')->offsetGet('id');
			$albumID12 = $this->albums_tests->add($albumID1, 'Album 1.2')->offsetGet('id');
			$albumID13 = $this->albums_tests->add($albumID1, 'Album 1.3')->offsetGet('id');
			$albumID111 = $this->albums_tests->add($albumID11, 'Album 1.1.1')->offsetGet('id');
			$albumID121 = $this->albums_tests->add($albumID12, 'Album 1.2.1')->offsetGet('id');
			$albumID131 = $this->albums_tests->add($albumID13, 'Album 1.3.1')->offsetGet('id');

			// Low-level tests on the DB layer to check of nested-set IDs are as expected
			$albumStat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$albumID1 => [
					'id' => $albumID1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 14,
				],
				$albumID11 => [
					'id' => $albumID11,
					'parent_id' => $albumID1,
					'_lft' => 2,
					'_rgt' => 5,
				],
				$albumID12 => [
					'id' => $albumID12,
					'parent_id' => $albumID1,
					'_lft' => 6,
					'_rgt' => 9,
				],
				$albumID13 => [
					'id' => $albumID13,
					'parent_id' => $albumID1,
					'_lft' => 10,
					'_rgt' => 13,
				],
				$albumID111 => [
					'id' => $albumID111,
					'parent_id' => $albumID11,
					'_lft' => 3,
					'_rgt' => 4,
				],
				$albumID121 => [
					'id' => $albumID121,
					'parent_id' => $albumID12,
					'_lft' => 7,
					'_rgt' => 8,
				],
				$albumID131 => [
					'id' => $albumID131,
					'parent_id' => $albumID13,
					'_lft' => 11,
					'_rgt' => 12,
				],
			], $albumStat);

			// Now let's do the multi-delete of a sub-forest
			$this->albums_tests->delete([$albumID11, $albumID13]);

			// Re-check on the lowest level
			$albumStat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$albumID1 => [
					'id' => $albumID1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 6,
				],
				$albumID12 => [
					'id' => $albumID12,
					'parent_id' => $albumID1,
					'_lft' => 2,
					'_rgt' => 5,
				],
				$albumID121 => [
					'id' => $albumID121,
					'parent_id' => $albumID12,
					'_lft' => 3,
					'_rgt' => 4,
				],
			], $albumStat);
		} finally {
			Auth::logout();
			Session::flush();
		}
	}

	/**
	 * Tests that the nested-set model remains consistent for a multi-delete of a forest.
	 *
	 * This tests considers the following album layout (the `_lft`, `_rgt`
	 * indices of the nested set model are illustrated):
	 *
	 *                             (root)
	 *                               |
	 *                            Album 1
	 *                            ( 1,14)
	 *                               |
	 *            +------------------+------------------+
	 *            |                  |                  |
	 *      Sub-Album 1.1      Sub-Album 1.2      Sub-Album 1.3
	 *          (2,5)              (6,9)             (10,13)
	 *            |                  |                  |
	 *     Sub-Album 1.1.1    Sub-Album 1.2.1    Sub-Album 1.3.1
	 *          (3,4)              (7,8)             (11,12)
	 *
	 * We then do a merge for album 1.1 and 1.2
	 * and expect the nested-set model to be updated like
	 *
	 *                             (root)
	 *                               |
	 *                            Album 1
	 *                            ( 1,12)
	 *                               |
	 *            +------------------+------------------+
	 *            |                                     |
	 *      Sub-Album 1.1                         Sub-Album 1.3
	 *          (2,7)                                (8,11)
	 *            |                                     |
	 *            +------------------+                  |
	 *     Sub-Album 1.1.1    Sub-Album 1.2.1    Sub-Album 1.3.1
	 *          (3,4)              (5,6)             (9,10)
	 *
	 * @return void
	 */
	public function testMerge(): void
	{
		try {
			// In order check the (_lft,_rgt)-indices we need deterministic
			// indices.
			// Hence, we must ensure that there are no left-overs from previous
			// tests.
			static::assertDatabaseCount('base_albums', 0);

			Auth::loginUsingId(1);

			// Create the test layout
			$albumID1 = $this->albums_tests->add(null, 'Album 1')->offsetGet('id');
			$albumID11 = $this->albums_tests->add($albumID1, 'Album 1.1')->offsetGet('id');
			$albumID12 = $this->albums_tests->add($albumID1, 'Album 1.2')->offsetGet('id');
			$albumID13 = $this->albums_tests->add($albumID1, 'Album 1.3')->offsetGet('id');
			$albumID111 = $this->albums_tests->add($albumID11, 'Album 1.1.1')->offsetGet('id');
			$albumID121 = $this->albums_tests->add($albumID12, 'Album 1.2.1')->offsetGet('id');
			$albumID131 = $this->albums_tests->add($albumID13, 'Album 1.3.1')->offsetGet('id');

			// Low-level tests on the DB layer to check of nested-set IDs are as expected
			$albumStat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$albumID1 => [
					'id' => $albumID1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 14,
				],
				$albumID11 => [
					'id' => $albumID11,
					'parent_id' => $albumID1,
					'_lft' => 2,
					'_rgt' => 5,
				],
				$albumID12 => [
					'id' => $albumID12,
					'parent_id' => $albumID1,
					'_lft' => 6,
					'_rgt' => 9,
				],
				$albumID13 => [
					'id' => $albumID13,
					'parent_id' => $albumID1,
					'_lft' => 10,
					'_rgt' => 13,
				],
				$albumID111 => [
					'id' => $albumID111,
					'parent_id' => $albumID11,
					'_lft' => 3,
					'_rgt' => 4,
				],
				$albumID121 => [
					'id' => $albumID121,
					'parent_id' => $albumID12,
					'_lft' => 7,
					'_rgt' => 8,
				],
				$albumID131 => [
					'id' => $albumID131,
					'parent_id' => $albumID13,
					'_lft' => 11,
					'_rgt' => 12,
				],
			], $albumStat);

			// Now let's do the multi-delete of a sub-forest
			$this->albums_tests->merge([$albumID12], $albumID11);

			// Re-check on the lowest level
			$albumStat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$albumID1 => [
					'id' => $albumID1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 12,
				],
				$albumID11 => [
					'id' => $albumID11,
					'parent_id' => $albumID1,
					'_lft' => 2,
					'_rgt' => 7,
				],
				$albumID13 => [
					'id' => $albumID13,
					'parent_id' => $albumID1,
					'_lft' => 8,
					'_rgt' => 11,
				],
				$albumID111 => [
					'id' => $albumID111,
					'parent_id' => $albumID11,
					'_lft' => 3,
					'_rgt' => 4,
				],
				$albumID121 => [
					'id' => $albumID121,
					'parent_id' => $albumID11,
					'_lft' => 5,
					'_rgt' => 6,
				],
				$albumID131 => [
					'id' => $albumID131,
					'parent_id' => $albumID13,
					'_lft' => 9,
					'_rgt' => 10,
				],
			], $albumStat);
		} finally {
			Auth::logout();
			Session::flush();
		}
	}

	public function testTrueNegative(): void
	{
		Auth::loginUsingId(1);

		$this->albums_tests->set_description('-1', 'new description', 422);
		$this->albums_tests->set_description('abcdefghijklmnopqrstuvwx', 'new description', 404);
		$this->albums_tests->set_protection_policy(id: '-1', expectedStatusCode: 422);
		$this->albums_tests->set_protection_policy(id: 'abcdefghijklmnopqrstuvwx', expectedStatusCode: 404);

		Auth::logout();
		Session::flush();
	}

	public function testAlbumTree(): void
	{
		$albumSortingColumn = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_COL);
		$albumSortingOrder = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_ORDER);

		try {
			Auth::loginUsingId(1);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, 'title');
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');

			// Sic! This out-of-order creation of albums is on purpose in order to
			// catch errors where the album tree is accidentally ordered as
			// expected, because we created the albums in correct order
			$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
			$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
			$albumID12 = $this->albums_tests->add($albumID1, 'Test Album 1.2')->offsetGet('id');
			$albumID21 = $this->albums_tests->add($albumID2, 'Test Album 2.1')->offsetGet('id');
			$albumID11 = $this->albums_tests->add($albumID1, 'Test Album 1.1')->offsetGet('id');

			$responseForTree = $this->root_album_tests->getTree();
			$responseForTree->assertJson([
				'albums' => [[
					'id' => $albumID1,
					'title' => 'Test Album 1',
					'parent_id' => null,
					'albums' => [[
						'id' => $albumID11,
						'title' => 'Test Album 1.1',
						'parent_id' => $albumID1,
						'albums' => [],
					], [
						'id' => $albumID12,
						'title' => 'Test Album 1.2',
						'parent_id' => $albumID1,
						'albums' => [],
					]],
				], [
					'id' => $albumID2,
					'title' => 'Test Album 2',
					'parent_id' => null,
					'albums' => [[
						'id' => $albumID21,
						'title' => 'Test Album 2.1',
						'parent_id' => $albumID2,
						'albums' => [],
					]],
				]],
				'shared_albums' => [],
			]);
		} finally {
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, $albumSortingColumn);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, $albumSortingOrder);
			Auth::logout();
			Session::flush();
		}
	}

	public function testAddAlbumByNonAdminUserWithoutUploadPrivilege(): void
	{
		Auth::loginUsingId(1);
		$userID = $this->users_tests->add('Test user', 'Test password', false)->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID);
		$this->albums_tests->add(null, 'Test Album', 403);
	}

	public function testAddAlbumByNonAdminUserWithUploadPrivilege(): void
	{
		Auth::loginUsingId(1);
		$userID = $this->users_tests->add('Test user', 'Test password')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID);
		$this->albums_tests->add(null, 'Test Album');
	}

	public function testEditAlbumByNonOwner(): void
	{
		Auth::loginUsingId(1);
		$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID1);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->sharing_tests->add([$albumID], [$userID2]);
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID2);
		$this->albums_tests->set_title($albumID, 'New title for test album', 403);
	}

	public function testEditAlbumByOwner(): void
	{
		Auth::loginUsingId(1);
		$userID = $this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->albums_tests->set_title($albumID, 'New title for test album');
		// Set password
		$this->albums_tests->set_protection_policy(id: $albumID,
			grants_full_photo_access: false,
			is_public: true,
			is_link_required: false,
			is_nsfw: false,
			grants_downloadable: true,
			password: 'PASSWORD');
		Auth::logout();
		Session::flush();

		// check password is required
		$this->albums_tests->get($albumID, 401);

		// We remove the password if it was set
		Auth::loginUsingId($userID);
		$this->albums_tests->set_protection_policy(id: $albumID, password: '');

		Auth::logout();
		Session::flush();
		$this->albums_tests->get($albumID);
	}

	public function testEditAlbumCopyright(): void
	{
		Auth::loginUsingId(1);
		$this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');

		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->albums_tests->set_copyright($albumID, 'Test copyright value');

		Auth::logout();
		Session::flush();
	}

	public function testDeleteMultipleAlbumsByAnonUser(): void
	{
		Auth::loginUsingId(1);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		$this->albums_tests->delete([$albumID1, $albumID2], 401);
	}

	public function testDeleteMultipleAlbumsByNonAdminUserWithoutUploadPrivilege(): void
	{
		Auth::loginUsingId(1);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$userID = $this->users_tests->add('Test user', 'Test password', false)->offsetGet('id');
		$this->sharing_tests->add([$albumID1, $albumID2], [$userID]);
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID);
		$this->albums_tests->delete([$albumID1, $albumID2], 403);
	}

	public function testDeleteMultipleAlbumsByNonOwner(): void
	{
		Auth::loginUsingId(1);
		$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID1);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$this->sharing_tests->add([$albumID1, $albumID2], [$userID2]);
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID2);
		$this->albums_tests->delete([$albumID1, $albumID2], 403);
	}

	public function testDeleteMultipleAlbumsByOwner(): void
	{
		Auth::loginUsingId(1);
		$userID = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($userID);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$this->albums_tests->delete([$albumID1, $albumID2]);
	}

	/**
	 * Creates a (regular) album, put some photos in it, tags some of them,
	 * creates a corresponding tag album and deletes the tag album again.
	 *
	 * This test ensures that only and ONLY the tag album is deleted.
	 *
	 * In particular, the test assures:
	 *  - deleting the tag album does not delete the photos inside it
	 *  - deleting the tah album does not delete the regular album which
	 *    contains the tagged photos
	 *
	 * Test for issue
	 * [LycheeOrg/Lychee#1472](https://github.com/LycheeOrg/Lychee/issues/1472).
	 *
	 * @return void
	 */
	public function testDeleteNonEmptyTagAlbumWithPhotosFromRegularAlbum(): void
	{
		Auth::loginUsingId(1);
		$regularAlbumID = $this->albums_tests->add(null, 'Regular Album for Delete Test')->offsetGet('id');
		$photoID = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $regularAlbumID
		)->offsetGet('id');
		$this->photos_tests->set_tag([$photoID], ['tag-for-delete-test']);
		$tagAlbumID = $this->albums_tests->addByTags('Tag Album for Delete Test', ['tag-for-delete-test'])->offsetGet('id');

		// Ensure that the photo is actually part of the tag album and that
		// we are testing what we want to test
		$this->albums_tests->get($tagAlbumID)->assertJson([]);

		$this->albums_tests->delete([$tagAlbumID]);

		// Ensure that the regular album and the photo are still there
		$this->albums_tests->get($regularAlbumID);
		$this->photos_tests->get($photoID);
	}

	/**
	 * Creates an extra User.
	 * Creates a (regular) album, put a photo in it.
	 * Log are extra user, and try to set the cover of the album, expect to fail.
	 *
	 * @return void
	 */
	public function testSetCoverByNonOwner()
	{
		Auth::loginUsingId(1);
		$userID = $this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$albumID
		)->offsetGet('id');
		Auth::logout();
		Session::flush();

		Auth::loginUsingId($userID);
		$this->albums_tests->set_cover($albumID, $photoID1, 403);
	}

	/**
	 * Creates a (regular) album, put 2 photos in it.
	 * Get original cover_id.
	 * Set cover of album to photo 1, check that cover_id is photo1.
	 * Set cover of album to photo 2, check that cover_id is photo2.
	 * Unset cover of album, check that cover_id is back to original.
	 *
	 * @return void
	 */
	public function testSetCoverByOwner()
	{
		Auth::loginUsingId(1);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$albumID
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_HOCHUFERWEG),
			$albumID
		)->offsetGet('id');
		$initialCoverID = $this->albums_tests->get($albumID)->offsetGet('cover_id');

		$this->albums_tests->set_cover($albumID, $photoID1);
		$coverID = $this->albums_tests->get($albumID)->offsetGet('cover_id');
		self::assertEquals($photoID1, $coverID);

		$this->albums_tests->set_cover($albumID, $photoID2);
		$coverID = $this->albums_tests->get($albumID)->offsetGet('cover_id');
		self::assertEquals($photoID2, $coverID);

		$this->albums_tests->set_cover($albumID, null);
		$coverID = $this->albums_tests->get($albumID)->offsetGet('cover_id');
		self::assertEquals($initialCoverID, $coverID);

		Auth::logout();
		Session::flush();
	}

	/**
	 * Creates an extra User.
	 * Creates a (regular) album, put a photo in it.
	 * Log are extra user, and try to set the header of the album, expect to fail.
	 *
	 * @return void
	 */
	public function testSetHeaderByNonOwner()
	{
		Auth::loginUsingId(1);
		$userID = $this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$albumID
		)->offsetGet('id');
		Auth::logout();
		Session::flush();

		Auth::loginUsingId($userID);
		$this->albums_tests->set_header($albumID, $photoID1, 403);
	}

	/**
	 * Creates a (regular) album, put 2 photos in it.
	 * Get original header_id.
	 * Set header of album to photo 1, check that header_id is photo1.
	 * Set header of album to photo 2, check that header_id is photo2.
	 * Unset header of album, check that header_id is back to original.
	 *
	 * @return void
	 */
	public function testSetHeaderByOwner()
	{
		Auth::loginUsingId(1);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photoID1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$albumID
		)->offsetGet('id');
		$photoID2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_HOCHUFERWEG),
			$albumID
		)->offsetGet('id');
		$initialHeaderID = $this->albums_tests->get($albumID)->offsetGet('header_id');

		$this->albums_tests->set_header($albumID, $photoID1);
		$headerID = $this->albums_tests->get($albumID)->offsetGet('header_id');
		self::assertEquals($photoID1, $headerID);

		$this->albums_tests->set_header($albumID, $photoID2);
		$headerID = $this->albums_tests->get($albumID)->offsetGet('header_id');
		self::assertEquals($photoID2, $headerID);

		$this->albums_tests->set_header($albumID, null);
		$headerID = $this->albums_tests->get($albumID)->offsetGet('header_id');
		self::assertEquals($initialHeaderID, $headerID);

		Auth::logout();
		Session::flush();
	}

	/**
	 * Check that deleting in Unsorted results in removing Unsorted pictures.
	 *
	 * @return void
	 */
	public function testDeleteUnsorted(): void
	{
		Auth::loginUsingId(1);
		$id = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		$this->photos_tests->get($id);

		$this->clearCachedSmartAlbums();
		$this->albums_tests->get(UnsortedAlbum::ID, 200, $id);
		$this->albums_tests->delete([UnsortedAlbum::ID], 204);
		$this->clearCachedSmartAlbums();
		$this->albums_tests->get(UnsortedAlbum::ID, 200, null, $id);
		$this->photos_tests->get($id, 404);

		Auth::logout();
		Session::flush();
	}

	public function testHiddenSmartAlbums(): void
	{
		Auth::loginUsingId(1);

		$this->clearCachedSmartAlbums();
		Configs::set(self::ENABLE_UNSORTED, true);
		Configs::set(self::ENABLE_STARRED, true);
		Configs::set(self::ENABLE_RECENT, true);
		Configs::set(self::ENABLE_ON_THIS_DAY, true);
		$response = $this->postJson('/api/Albums::get');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [
				'unsorted' => [],
				'starred' => [],
				'recent' => [],
				'on_this_day' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);

		$this->clearCachedSmartAlbums();
		Configs::set(self::ENABLE_UNSORTED, false);
		Configs::set(self::ENABLE_STARRED, false);
		Configs::set(self::ENABLE_RECENT, false);
		Configs::set(self::ENABLE_ON_THIS_DAY, false);
		$response = $this->postJson('/api/Albums::get');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$response->assertDontSee('unsorted');
		$response->assertDontSee('starred');
		$response->assertDontSee('recent');
		$response->assertDontSee('on_this_day');

		$this->clearCachedSmartAlbums();
		Configs::set(self::ENABLE_UNSORTED, true);
		$response = $this->postJson('/api/Albums::get');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [
				'unsorted' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$response->assertDontSee('starred');
		$response->assertDontSee('recent');
		$response->assertDontSee('on_this_day');

		$this->clearCachedSmartAlbums();
		Configs::set(self::ENABLE_STARRED, true);
		$response = $this->postJson('/api/Albums::get');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [
				'unsorted' => [],
				'starred' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$response->assertDontSee('recent');
		$response->assertDontSee('on_this_day');

		$this->clearCachedSmartAlbums();
		Configs::set(self::ENABLE_RECENT, true);
		$response = $this->postJson('/api/Albums::get');
		$this->assertOk($response);
		$response->assertJson([
			'smart_albums' => [
				'unsorted' => [],
				'starred' => [],
				'recent' => [],
			],
			'tag_albums' => [],
			'albums' => [],
			'shared_albums' => [],
		]);
		$response->assertDontSee('on_this_day');
		$this->clearCachedSmartAlbums();
		Configs::set(self::ENABLE_ON_THIS_DAY, true);

		Auth::logout();
		Session::flush();
	}

	public function testOnThisDayAlbumWhenThereIsPhotoTakenAtCurrentMonthAndDay(): void
	{
		Auth::loginUsingId(1);
		$today = CarbonImmutable::today();
		$photoID = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		DB::table('photos')
			->where('id', '=', $photoID)
			->update([
				'taken_at' => $today->subYear()->format('Y-m-d H:i:s.u'),
				'created_at' => $today->subDay()->format('Y-m-d H:i:s.u'),
			]);

		$response = $this->albums_tests->get(OnThisDayAlbum::ID, 200, 'photos');
		$response->assertSee($photoID);

		$this->clearCachedSmartAlbums();
		Auth::logout();
	}

	public function testOnThisDayAlbumWhenThereIsPhotoCreatedAtCurrentMonthAndDay(): void
	{
		Auth::loginUsingId(1);
		$today = CarbonImmutable::today();
		$photoID = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		DB::table('photos')
			->where('id', '=', $photoID)
			->update([
				'taken_at' => null,
				'created_at' => $today->subYear()->format('Y-m-d H:i:s.u'),
			]);
		$response = $this->albums_tests->get(OnThisDayAlbum::ID, 200, 'photos');
		$response->assertSee($photoID);

		$this->clearCachedSmartAlbums();
		Auth::logout();
	}

	public function testOnThisDayAlbumWhenIsEmpty(): void
	{
		Auth::loginUsingId(1);
		$today = CarbonImmutable::today();
		$photoID = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		DB::table('photos')
			->where('id', '=', $photoID)
			->update([
				'taken_at' => null,
				'created_at' => $today->subDay()->format('Y-m-d H:i:s.u'),
			]);

		$response = $this->albums_tests->get(OnThisDayAlbum::ID, 200, 'photos');
		$response->assertDontSee($photoID);

		$this->clearCachedSmartAlbums();
		Auth::logout();
	}

	/**
	 * 1. Set default album created as public
	 * 2. Create album
	 * 3. logout
	 * 4. check visibility
	 * 5. Set.
	 *
	 * @return void
	 */
	public function testAddPublicByDefault(): void
	{
		$defaultProtectionType = Configs::getValueAsEnum(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::class);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::PUBLIC);
		Auth::loginUsingId(1);
		$albumID1 = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		Auth::logout();
		$root = $this->root_album_tests->get();
		$root->assertSee($albumID1);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::INHERIT);
		Auth::loginUsingId(1);
		$albumID2 = $this->albums_tests->add($albumID1, 'Test Album 2')->offsetGet('id');
		Auth::logout();
		$album1 = $this->albums_tests->get($albumID1);
		$album1->assertSee($albumID2);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::PRIVATE);
		Auth::loginUsingId(1);
		$albumID3 = $this->albums_tests->add($albumID1, 'Test Album 3')->offsetGet('id');
		Auth::logout();
		$album1 = $this->albums_tests->get($albumID1);
		$album1->assertSee($albumID2);
		$album1->assertDontSee($albumID3);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, $defaultProtectionType);
	}

	/**
	 * Test that setting NSFW via the Protection Policy works.
	 * 1. Create album
	 * 2. check nsfw is false
	 * 3. set nsfw to true
	 * 4. check nsfw is true
	 * 5. set nsfw to false
	 * 6. check nsfw is false.
	 *
	 * @return void
	 */
	public function testNSFWViaProtectionPolicy(): void
	{
		Auth::loginUsingId(1);
		$albumID1 = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$res = $this->albums_tests->get($albumID1);
		$res->assertJson(['policy' => ['is_nsfw' => false]]);
		$this->albums_tests->set_protection_policy(id: $albumID1, is_nsfw: true);
		$res = $this->albums_tests->get($albumID1);
		$res->assertJson(['policy' => ['is_nsfw' => true]]);
		$this->albums_tests->set_protection_policy(id: $albumID1, is_nsfw: false);
		$res = $this->albums_tests->get($albumID1);
		$res->assertJson(['policy' => ['is_nsfw' => false]]);
		Auth::logout();
	}
}
