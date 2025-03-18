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

		$album_i_d1 = $this->albums_tests->add(null, 'test_album')->offsetGet('id');
		$album_i_d2 = $this->albums_tests->add(null, 'test_album2')->offsetGet('id');
		$album_i_d3 = $this->albums_tests->add(null, 'test_album3')->offsetGet('id');
		$album_tag_i_d1 = $this->albums_tests->addByTags('test_tag_album1', ['test'])->offsetGet('id');

		$this->albums_tests->set_tags($album_tag_i_d1, ['test', 'cool_new_tag', 'second_new_tag']);
		$response = $this->albums_tests->get($album_tag_i_d1);
		$response->assertJson([
			'show_tags' => ['test', 'cool_new_tag', 'second_new_tag'],
		]);

		$this->root_album_tests->get(200, $album_i_d1);
		$this->root_album_tests->get(200, $album_i_d2);
		$this->root_album_tests->get(200, $album_i_d3);
		$this->root_album_tests->get(200, $album_tag_i_d1);

		$this->albums_tests->move([$album_tag_i_d1], $album_i_d3, 404);
		$this->albums_tests->move([$album_i_d3], $album_i_d2);
		$this->albums_tests->move([$album_i_d2], $album_i_d1);
		$this->albums_tests->move([$album_i_d3], null);

		/*
		 * try to get a non-existing album
		 */
		$this->albums_tests->get('abcdefghijklmnopqrstuvwx', 404);

		$response = $this->albums_tests->get($album_i_d1);
		$response->assertJson([
			'id' => $album_i_d1,
			'description' => null,
			'title' => 'test_album',
			'albums' => [['id' => $album_i_d2]],
			'num_subalbums' => 1,
		]);

		$this->albums_tests->set_title($album_i_d1, 'NEW_TEST');
		$this->albums_tests->set_description($album_i_d1, 'new description');
		$this->albums_tests->set_license($album_i_d1, 'WTFPL', 422);
		$this->albums_tests->set_license($album_i_d1, 'reserved');
		$this->albums_tests->set_sorting($album_i_d1, 'title', 'ASC');

		/**
		 * Let's see if the info changed.
		 */
		$response = $this->albums_tests->get($album_i_d1);
		$response->assertJson([
			'id' => $album_i_d1,
			'description' => 'new description',
			'title' => 'NEW_TEST',
		]);

		$this->albums_tests->set_sorting($album_i_d1, '', 'ASC');

		/*
		 * Flush the session to see if we can access the album
		 */
		Auth::logout();
		Session::flush();

		/*
		 * Let's try to get the info of the album we just created.
		 */
		$this->albums_tests->unlock($album_i_d1, '', 422);
		$this->albums_tests->unlock($album_i_d1, 'wrong-password', 403);
		$this->albums_tests->get($album_i_d1, 401);

		Auth::loginUsingId(1);

		/*
		 * Let's try to delete this album.
		 */
		$this->albums_tests->delete([$album_i_d1]);
		$this->albums_tests->delete([$album_i_d3]);
		$this->albums_tests->delete([$album_tag_i_d1]);

		/*
		 * Because we deleted the album, we should not see it anymore.
		 */
		$this->root_album_tests->get(200, null, $album_i_d1);
		$this->root_album_tests->get(200, null, $album_i_d3);
		$this->root_album_tests->get(200, null, $album_tag_i_d1);

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
			$album_i_d1 = $this->albums_tests->add(null, 'Album 1')->offsetGet('id');
			$album_i_d11 = $this->albums_tests->add($album_i_d1, 'Album 1.1')->offsetGet('id');
			$album_i_d12 = $this->albums_tests->add($album_i_d1, 'Album 1.2')->offsetGet('id');
			$album_i_d13 = $this->albums_tests->add($album_i_d1, 'Album 1.3')->offsetGet('id');
			$album_i_d111 = $this->albums_tests->add($album_i_d11, 'Album 1.1.1')->offsetGet('id');
			$album_i_d121 = $this->albums_tests->add($album_i_d12, 'Album 1.2.1')->offsetGet('id');
			$album_i_d131 = $this->albums_tests->add($album_i_d13, 'Album 1.3.1')->offsetGet('id');

			// Low-level tests on the DB layer to check of nested-set IDs are as expected
			$album_stat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$album_i_d1 => [
					'id' => $album_i_d1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 14,
				],
				$album_i_d11 => [
					'id' => $album_i_d11,
					'parent_id' => $album_i_d1,
					'_lft' => 2,
					'_rgt' => 5,
				],
				$album_i_d12 => [
					'id' => $album_i_d12,
					'parent_id' => $album_i_d1,
					'_lft' => 6,
					'_rgt' => 9,
				],
				$album_i_d13 => [
					'id' => $album_i_d13,
					'parent_id' => $album_i_d1,
					'_lft' => 10,
					'_rgt' => 13,
				],
				$album_i_d111 => [
					'id' => $album_i_d111,
					'parent_id' => $album_i_d11,
					'_lft' => 3,
					'_rgt' => 4,
				],
				$album_i_d121 => [
					'id' => $album_i_d121,
					'parent_id' => $album_i_d12,
					'_lft' => 7,
					'_rgt' => 8,
				],
				$album_i_d131 => [
					'id' => $album_i_d131,
					'parent_id' => $album_i_d13,
					'_lft' => 11,
					'_rgt' => 12,
				],
			], $album_stat);

			// Now let's do the multi-delete of a sub-forest
			$this->albums_tests->delete([$album_i_d11, $album_i_d13]);

			// Re-check on the lowest level
			$album_stat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$album_i_d1 => [
					'id' => $album_i_d1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 6,
				],
				$album_i_d12 => [
					'id' => $album_i_d12,
					'parent_id' => $album_i_d1,
					'_lft' => 2,
					'_rgt' => 5,
				],
				$album_i_d121 => [
					'id' => $album_i_d121,
					'parent_id' => $album_i_d12,
					'_lft' => 3,
					'_rgt' => 4,
				],
			], $album_stat);
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
			$album_i_d1 = $this->albums_tests->add(null, 'Album 1')->offsetGet('id');
			$album_i_d11 = $this->albums_tests->add($album_i_d1, 'Album 1.1')->offsetGet('id');
			$album_i_d12 = $this->albums_tests->add($album_i_d1, 'Album 1.2')->offsetGet('id');
			$album_i_d13 = $this->albums_tests->add($album_i_d1, 'Album 1.3')->offsetGet('id');
			$album_i_d111 = $this->albums_tests->add($album_i_d11, 'Album 1.1.1')->offsetGet('id');
			$album_i_d121 = $this->albums_tests->add($album_i_d12, 'Album 1.2.1')->offsetGet('id');
			$album_i_d131 = $this->albums_tests->add($album_i_d13, 'Album 1.3.1')->offsetGet('id');

			// Low-level tests on the DB layer to check of nested-set IDs are as expected
			$album_stat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$album_i_d1 => [
					'id' => $album_i_d1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 14,
				],
				$album_i_d11 => [
					'id' => $album_i_d11,
					'parent_id' => $album_i_d1,
					'_lft' => 2,
					'_rgt' => 5,
				],
				$album_i_d12 => [
					'id' => $album_i_d12,
					'parent_id' => $album_i_d1,
					'_lft' => 6,
					'_rgt' => 9,
				],
				$album_i_d13 => [
					'id' => $album_i_d13,
					'parent_id' => $album_i_d1,
					'_lft' => 10,
					'_rgt' => 13,
				],
				$album_i_d111 => [
					'id' => $album_i_d111,
					'parent_id' => $album_i_d11,
					'_lft' => 3,
					'_rgt' => 4,
				],
				$album_i_d121 => [
					'id' => $album_i_d121,
					'parent_id' => $album_i_d12,
					'_lft' => 7,
					'_rgt' => 8,
				],
				$album_i_d131 => [
					'id' => $album_i_d131,
					'parent_id' => $album_i_d13,
					'_lft' => 11,
					'_rgt' => 12,
				],
			], $album_stat);

			// Now let's do the multi-delete of a sub-forest
			$this->albums_tests->merge([$album_i_d12], $album_i_d11);

			// Re-check on the lowest level
			$album_stat = DB::table('albums')
				->get(['id', 'parent_id', '_lft', '_rgt'])
				->map(fn ($row) => get_object_vars($row))
				->keyBy('id')
				->toArray();
			static::assertEquals([
				$album_i_d1 => [
					'id' => $album_i_d1,
					'parent_id' => null,
					'_lft' => 1,
					'_rgt' => 12,
				],
				$album_i_d11 => [
					'id' => $album_i_d11,
					'parent_id' => $album_i_d1,
					'_lft' => 2,
					'_rgt' => 7,
				],
				$album_i_d13 => [
					'id' => $album_i_d13,
					'parent_id' => $album_i_d1,
					'_lft' => 8,
					'_rgt' => 11,
				],
				$album_i_d111 => [
					'id' => $album_i_d111,
					'parent_id' => $album_i_d11,
					'_lft' => 3,
					'_rgt' => 4,
				],
				$album_i_d121 => [
					'id' => $album_i_d121,
					'parent_id' => $album_i_d11,
					'_lft' => 5,
					'_rgt' => 6,
				],
				$album_i_d131 => [
					'id' => $album_i_d131,
					'parent_id' => $album_i_d13,
					'_lft' => 9,
					'_rgt' => 10,
				],
			], $album_stat);
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
		$album_sorting_column = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_COL);
		$album_sorting_order = Configs::getValueAsString(TestConstants::CONFIG_ALBUMS_SORTING_ORDER);

		try {
			Auth::loginUsingId(1);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, 'title');
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');

			// Sic! This out-of-order creation of albums is on purpose in order to
			// catch errors where the album tree is accidentally ordered as
			// expected, because we created the albums in correct order
			$album_i_d2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
			$album_i_d1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
			$album_i_d12 = $this->albums_tests->add($album_i_d1, 'Test Album 1.2')->offsetGet('id');
			$album_i_d21 = $this->albums_tests->add($album_i_d2, 'Test Album 2.1')->offsetGet('id');
			$album_i_d11 = $this->albums_tests->add($album_i_d1, 'Test Album 1.1')->offsetGet('id');

			$response_for_tree = $this->root_album_tests->getTree();
			$response_for_tree->assertJson([
				'albums' => [[
					'id' => $album_i_d1,
					'title' => 'Test Album 1',
					'parent_id' => null,
					'albums' => [[
						'id' => $album_i_d11,
						'title' => 'Test Album 1.1',
						'parent_id' => $album_i_d1,
						'albums' => [],
					], [
						'id' => $album_i_d12,
						'title' => 'Test Album 1.2',
						'parent_id' => $album_i_d1,
						'albums' => [],
					]],
				], [
					'id' => $album_i_d2,
					'title' => 'Test Album 2',
					'parent_id' => null,
					'albums' => [[
						'id' => $album_i_d21,
						'title' => 'Test Album 2.1',
						'parent_id' => $album_i_d2,
						'albums' => [],
					]],
				]],
				'shared_albums' => [],
			]);
		} finally {
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_COL, $album_sorting_column);
			Configs::set(TestConstants::CONFIG_ALBUMS_SORTING_ORDER, $album_sorting_order);
			Auth::logout();
			Session::flush();
		}
	}

	public function testAddAlbumByNonAdminUserWithoutUploadPrivilege(): void
	{
		Auth::loginUsingId(1);
		$user_i_d = $this->users_tests->add('Test user', 'Test password', false)->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d);
		$this->albums_tests->add(null, 'Test Album', 403);
	}

	public function testAddAlbumByNonAdminUserWithUploadPrivilege(): void
	{
		Auth::loginUsingId(1);
		$user_i_d = $this->users_tests->add('Test user', 'Test password')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d);
		$this->albums_tests->add(null, 'Test Album');
	}

	public function testEditAlbumByNonOwner(): void
	{
		Auth::loginUsingId(1);
		$user_i_d1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$user_i_d2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d1);
		$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->sharing_tests->add([$album_i_d], [$user_i_d2]);
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d2);
		$this->albums_tests->set_title($album_i_d, 'New title for test album', 403);
	}

	public function testEditAlbumByOwner(): void
	{
		Auth::loginUsingId(1);
		$user_i_d = $this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d);
		$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->albums_tests->set_title($album_i_d, 'New title for test album');
		// Set password
		$this->albums_tests->set_protection_policy(id: $album_i_d,
			grants_full_photo_access: false,
			is_public: true,
			is_link_required: false,
			is_nsfw: false,
			grants_downloadable: true,
			password: 'PASSWORD');
		Auth::logout();
		Session::flush();

		// check password is required
		$this->albums_tests->get($album_i_d, 401);

		// We remove the password if it was set
		Auth::loginUsingId($user_i_d);
		$this->albums_tests->set_protection_policy(id: $album_i_d, password: '');

		Auth::logout();
		Session::flush();
		$this->albums_tests->get($album_i_d);
	}

	public function testEditAlbumCopyright(): void
	{
		Auth::loginUsingId(1);
		$this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');

		$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->albums_tests->set_copyright($album_i_d, 'Test copyright value');

		Auth::logout();
		Session::flush();
	}

	public function testDeleteMultipleAlbumsByAnonUser(): void
	{
		Auth::loginUsingId(1);
		$album_i_d1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$album_i_d2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		$this->albums_tests->delete([$album_i_d1, $album_i_d2], 401);
	}

	public function testDeleteMultipleAlbumsByNonAdminUserWithoutUploadPrivilege(): void
	{
		Auth::loginUsingId(1);
		$album_i_d1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$album_i_d2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$user_i_d = $this->users_tests->add('Test user', 'Test password', false)->offsetGet('id');
		$this->sharing_tests->add([$album_i_d1, $album_i_d2], [$user_i_d]);
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d);
		$this->albums_tests->delete([$album_i_d1, $album_i_d2], 403);
	}

	public function testDeleteMultipleAlbumsByNonOwner(): void
	{
		Auth::loginUsingId(1);
		$user_i_d1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$user_i_d2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d1);
		$album_i_d1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$album_i_d2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$this->sharing_tests->add([$album_i_d1, $album_i_d2], [$user_i_d2]);
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d2);
		$this->albums_tests->delete([$album_i_d1, $album_i_d2], 403);
	}

	public function testDeleteMultipleAlbumsByOwner(): void
	{
		Auth::loginUsingId(1);
		$user_i_d = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		Auth::logout();
		Session::flush();
		Auth::loginUsingId($user_i_d);
		$album_i_d1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$album_i_d2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$this->albums_tests->delete([$album_i_d1, $album_i_d2]);
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
		$regular_album_i_d = $this->albums_tests->add(null, 'Regular Album for Delete Test')->offsetGet('id');
		$photo_i_d = $this->photos_tests->upload(
			self::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE), $regular_album_i_d
		)->offsetGet('id');
		$this->photos_tests->set_tag([$photo_i_d], ['tag-for-delete-test']);
		$tag_album_i_d = $this->albums_tests->addByTags('Tag Album for Delete Test', ['tag-for-delete-test'])->offsetGet('id');

		// Ensure that the photo is actually part of the tag album and that
		// we are testing what we want to test
		$this->albums_tests->get($tag_album_i_d)->assertJson([]);

		$this->albums_tests->delete([$tag_album_i_d]);

		// Ensure that the regular album and the photo are still there
		$this->albums_tests->get($regular_album_i_d);
		$this->photos_tests->get($photo_i_d);
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
		$user_i_d = $this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');
		$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$album_i_d
		)->offsetGet('id');
		Auth::logout();
		Session::flush();

		Auth::loginUsingId($user_i_d);
		$this->albums_tests->set_cover($album_i_d, $photo_i_d1, 403);
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
		$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$album_i_d
		)->offsetGet('id');
		$photo_i_d2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_HOCHUFERWEG),
			$album_i_d
		)->offsetGet('id');
		$initial_cover_i_d = $this->albums_tests->get($album_i_d)->offsetGet('cover_id');

		$this->albums_tests->set_cover($album_i_d, $photo_i_d1);
		$cover_i_d = $this->albums_tests->get($album_i_d)->offsetGet('cover_id');
		self::assertEquals($photo_i_d1, $cover_i_d);

		$this->albums_tests->set_cover($album_i_d, $photo_i_d2);
		$cover_i_d = $this->albums_tests->get($album_i_d)->offsetGet('cover_id');
		self::assertEquals($photo_i_d2, $cover_i_d);

		$this->albums_tests->set_cover($album_i_d, null);
		$cover_i_d = $this->albums_tests->get($album_i_d)->offsetGet('cover_id');
		self::assertEquals($initial_cover_i_d, $cover_i_d);

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
		$user_i_d = $this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');
		$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$album_i_d
		)->offsetGet('id');
		Auth::logout();
		Session::flush();

		Auth::loginUsingId($user_i_d);
		$this->albums_tests->set_header($album_i_d, $photo_i_d1, 403);
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
		$album_i_d = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$photo_i_d1 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			$album_i_d
		)->offsetGet('id');
		$photo_i_d2 = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_HOCHUFERWEG),
			$album_i_d
		)->offsetGet('id');
		$initial_header_i_d = $this->albums_tests->get($album_i_d)->offsetGet('header_id');

		$this->albums_tests->set_header($album_i_d, $photo_i_d1);
		$header_i_d = $this->albums_tests->get($album_i_d)->offsetGet('header_id');
		self::assertEquals($photo_i_d1, $header_i_d);

		$this->albums_tests->set_header($album_i_d, $photo_i_d2);
		$header_i_d = $this->albums_tests->get($album_i_d)->offsetGet('header_id');
		self::assertEquals($photo_i_d2, $header_i_d);

		$this->albums_tests->set_header($album_i_d, null);
		$header_i_d = $this->albums_tests->get($album_i_d)->offsetGet('header_id');
		self::assertEquals($initial_header_i_d, $header_i_d);

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
		$photo_i_d = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		DB::table('photos')
			->where('id', '=', $photo_i_d)
			->update([
				'taken_at' => $today->subYear()->format('Y-m-d H:i:s.u'),
				'created_at' => $today->subDay()->format('Y-m-d H:i:s.u'),
			]);

		$response = $this->albums_tests->get(OnThisDayAlbum::ID, 200, 'photos');
		$response->assertSee($photo_i_d);

		$this->clearCachedSmartAlbums();
		Auth::logout();
	}

	public function testOnThisDayAlbumWhenThereIsPhotoCreatedAtCurrentMonthAndDay(): void
	{
		Auth::loginUsingId(1);
		$today = CarbonImmutable::today();
		$photo_i_d = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		DB::table('photos')
			->where('id', '=', $photo_i_d)
			->update([
				'taken_at' => null,
				'created_at' => $today->subYear()->format('Y-m-d H:i:s.u'),
			]);
		$response = $this->albums_tests->get(OnThisDayAlbum::ID, 200, 'photos');
		$response->assertSee($photo_i_d);

		$this->clearCachedSmartAlbums();
		Auth::logout();
	}

	public function testOnThisDayAlbumWhenIsEmpty(): void
	{
		Auth::loginUsingId(1);
		$today = CarbonImmutable::today();
		$photo_i_d = $this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');

		DB::table('photos')
			->where('id', '=', $photo_i_d)
			->update([
				'taken_at' => null,
				'created_at' => $today->subDay()->format('Y-m-d H:i:s.u'),
			]);

		$response = $this->albums_tests->get(OnThisDayAlbum::ID, 200, 'photos');
		$response->assertDontSee($photo_i_d);

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
		$default_protection_type = Configs::getValueAsEnum(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::class);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::PUBLIC);
		Auth::loginUsingId(1);
		$album_i_d1 = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		Auth::logout();
		$root = $this->root_album_tests->get();
		$root->assertSee($album_i_d1);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::INHERIT);
		Auth::loginUsingId(1);
		$album_i_d2 = $this->albums_tests->add($album_i_d1, 'Test Album 2')->offsetGet('id');
		Auth::logout();
		$album1 = $this->albums_tests->get($album_i_d1);
		$album1->assertSee($album_i_d2);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, DefaultAlbumProtectionType::PRIVATE);
		Auth::loginUsingId(1);
		$album_i_d3 = $this->albums_tests->add($album_i_d1, 'Test Album 3')->offsetGet('id');
		Auth::logout();
		$album1 = $this->albums_tests->get($album_i_d1);
		$album1->assertSee($album_i_d2);
		$album1->assertDontSee($album_i_d3);

		Configs::set(TestConstants::CONFIG_DEFAULT_ALBUM_PROTECTION, $default_protection_type);
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
		$album_i_d1 = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$res = $this->albums_tests->get($album_i_d1);
		$res->assertJson(['policy' => ['is_nsfw' => false]]);
		$this->albums_tests->set_protection_policy(id: $album_i_d1, is_nsfw: true);
		$res = $this->albums_tests->get($album_i_d1);
		$res->assertJson(['policy' => ['is_nsfw' => true]]);
		$this->albums_tests->set_protection_policy(id: $album_i_d1, is_nsfw: false);
		$res = $this->albums_tests->get($album_i_d1);
		$res->assertJson(['policy' => ['is_nsfw' => false]]);
		Auth::logout();
	}
}
