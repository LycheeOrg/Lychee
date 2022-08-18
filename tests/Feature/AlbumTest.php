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
use App\Models\Configs;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\RootAlbumUnitTest;
use Tests\Feature\Lib\SharingUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\Feature\Traits\InteractWithSmartAlbums;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\TestCase;

class AlbumTest extends TestCase
{
	use InteractWithSmartAlbums;
	use RequiresEmptyAlbums;
	use RequiresEmptyUsers;

	protected AlbumsUnitTest $albums_tests;
	protected RootAlbumUnitTest $root_album_tests;
	protected UsersUnitTest $users_tests;
	protected SharingUnitTest $sharing_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->setUpRequiresEmptyAlbums();
		$this->albums_tests = new AlbumsUnitTest($this);
		$this->root_album_tests = new RootAlbumUnitTest($this);
		$this->users_tests = new UsersUnitTest($this);
		$this->sharing_tests = new SharingUnitTest($this);
	}

	public function tearDown(): void
	{
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
		$this->albums_tests->get(PublicAlbum::ID, 401);
		$this->albums_tests->get(UnsortedAlbum::ID, 401);

		// Ensure that we get proper 404 (not found) response for a
		// non-existing album, not a false 403 (forbidden) response
		$this->albums_tests->get('abcdefghijklmnopqrstuvwx', 404);
	}

	public function testAddReadLogged(): void
	{
		AccessControl::log_as_id(0);
		$this->clearCachedSmartAlbums();

		$this->albums_tests->get(RecentAlbum::ID);
		$this->albums_tests->get(StarredAlbum::ID);
		$this->albums_tests->get(PublicAlbum::ID);
		$this->albums_tests->get(UnsortedAlbum::ID);

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
		AccessControl::logout();

		/*
		 * Let's try to get the info of the album we just created.
		 */
		$this->albums_tests->unlock($albumID1, '', 422);
		$this->albums_tests->unlock($albumID1, 'wrong-password', 403);
		$this->albums_tests->get($albumID1, 401);

		AccessControl::log_as_id(0);

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

		AccessControl::logout();
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

			AccessControl::log_as_id(0);

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
			AccessControl::logout();
		}
	}

	public function testTrueNegative(): void
	{
		AccessControl::log_as_id(0);

		$this->albums_tests->set_description('-1', 'new description', 422);
		$this->albums_tests->set_description('abcdefghijklmnopqrstuvwx', 'new description', 404);
		$this->albums_tests->set_protection_policy('-1', true, true, false, false, true, true, false, 0, null, 422);
		$this->albums_tests->set_protection_policy('abcdefghijklmnopqrstuvwx', true, true, false, false, true, true, false, 0, null, 404);

		AccessControl::logout();
	}

	public function testAlbumTree(): void
	{
		$albumSortingColumn = Configs::getValueAsString(self::CONFIG_ALBUMS_SORTING_COL);
		$albumSortingOrder = Configs::getValueAsString(self::CONFIG_ALBUMS_SORTING_ORDER);

		try {
			AccessControl::log_as_id(0);
			Configs::set(self::CONFIG_ALBUMS_SORTING_COL, 'title');
			Configs::set(self::CONFIG_ALBUMS_SORTING_ORDER, 'ASC');

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
			Configs::set(self::CONFIG_ALBUMS_SORTING_COL, $albumSortingColumn);
			Configs::set(self::CONFIG_ALBUMS_SORTING_ORDER, $albumSortingOrder);
			AccessControl::logout();
		}
	}

	public function testAddAlbumByNonAdminUserWithoutUploadPrivilege(): void
	{
		AccessControl::log_as_id(0);
		$userID = $this->users_tests->add('Test user', 'Test password', false)->offsetGet('id');
		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->albums_tests->add(null, 'Test Album', 403);
	}

	public function testAddAlbumByNonAdminUserWithUploadPrivilege(): void
	{
		AccessControl::log_as_id(0);
		$userID = $this->users_tests->add('Test user', 'Test password')->offsetGet('id');
		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->albums_tests->add(null, 'Test Album');
	}

	public function testEditAlbumByNonOwner(): void
	{
		AccessControl::log_as_id(0);
		$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		AccessControl::logout();
		AccessControl::log_as_id($userID1);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->sharing_tests->add([$albumID], [$userID2]);
		AccessControl::logout();
		AccessControl::log_as_id($userID2);
		$this->albums_tests->set_title($albumID, 'New title for test album', 403);
	}

	public function testEditAlbumByOwner(): void
	{
		AccessControl::log_as_id(0);
		$userID = $this->users_tests->add('Test user', 'Test password 1')->offsetGet('id');
		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$albumID = $this->albums_tests->add(null, 'Test Album')->offsetGet('id');
		$this->albums_tests->set_title($albumID, 'New title for test album');
	}

	public function testDeleteMultipleAlbumsByAnonUser(): void
	{
		AccessControl::log_as_id(0);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		AccessControl::logout();
		$this->albums_tests->delete([$albumID1, $albumID2], 401);
	}

	public function testDeleteMultipleAlbumsByNonAdminUserWithoutUploadPrivilege(): void
	{
		AccessControl::log_as_id(0);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$userID = $this->users_tests->add('Test user', 'Test password', false)->offsetGet('id');
		$this->sharing_tests->add([$albumID1, $albumID2], [$userID]);
		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$this->albums_tests->delete([$albumID1, $albumID2], 403);
	}

	public function testDeleteMultipleAlbumsByNonOwner(): void
	{
		AccessControl::log_as_id(0);
		$userID1 = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		$userID2 = $this->users_tests->add('Test user 2', 'Test password 2')->offsetGet('id');
		AccessControl::logout();
		AccessControl::log_as_id($userID1);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$this->sharing_tests->add([$albumID1, $albumID2], [$userID2]);
		AccessControl::logout();
		AccessControl::log_as_id($userID2);
		$this->albums_tests->delete([$albumID1, $albumID2], 403);
	}

	public function testDeleteMultipleAlbumsByOwner(): void
	{
		AccessControl::log_as_id(0);
		$userID = $this->users_tests->add('Test user 1', 'Test password 1')->offsetGet('id');
		AccessControl::logout();
		AccessControl::log_as_id($userID);
		$albumID1 = $this->albums_tests->add(null, 'Test Album 1')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'Test Album 2')->offsetGet('id');
		$this->albums_tests->delete([$albumID1, $albumID2]);
	}
}
