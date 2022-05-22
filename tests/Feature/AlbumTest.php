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
use Illuminate\Support\Facades\DB;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\TestCase;

class AlbumTest extends TestCase
{
	protected AlbumsUnitTest $albums_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->albums_tests = new AlbumsUnitTest($this);
	}

	/**
	 * Test album functions.
	 *
	 * @return void
	 */
	public function testAddNotLogged(): void
	{
		$this->albums_tests->add(null, 'test_album', 401);

		$this->albums_tests->get('recent', 401);
		$this->albums_tests->get('starred', 401);
		$this->albums_tests->get('public', 401);
		$this->albums_tests->get('unsorted', 401);

		// Ensure that we get proper 404 (not found) response for a
		// non-existing album, not a false 403 (forbidden) response
		$this->albums_tests->get('abcdefghijklmnopqrstuvwx', 404);
	}

	public function testAddReadLogged(): void
	{
		AccessControl::log_as_id(0);

		$this->albums_tests->get('recent');
		$this->albums_tests->get('starred');
		$this->albums_tests->get('public');
		$this->albums_tests->get('unsorted');

		$albumID1 = $this->albums_tests->add(null, 'test_album')->offsetGet('id');
		$albumID2 = $this->albums_tests->add(null, 'test_album2')->offsetGet('id');
		$albumID3 = $this->albums_tests->add(null, 'test_album3')->offsetGet('id');
		$albumTagID1 = $this->albums_tests->addByTags('test_tag_album1', ['test'])->offsetGet('id');

		$this->albums_tests->set_tags($albumTagID1, ['test', 'cool_new_tag', 'second_new_tag']);
		$response = $this->albums_tests->get($albumTagID1);
		$response->assertJson([
			'show_tags' => ['test', 'cool_new_tag', 'second_new_tag'],
		]);

		$this->albums_tests->see_in_albums($albumID1);
		$this->albums_tests->see_in_albums($albumID2);
		$this->albums_tests->see_in_albums($albumID3);
		$this->albums_tests->see_in_albums($albumTagID1);

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
		$this->albums_tests->dont_see_in_albums($albumID1);
		$this->albums_tests->dont_see_in_albums($albumID3);
		$this->albums_tests->dont_see_in_albums($albumTagID1);

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
			// Clean-up any left-overs
			DB::table('albums')->orderBy('_lft', 'desc')->delete();
			DB::table('base_albums')->delete();
			AccessControl::logout();
		}
	}

	public function testTrueNegative(): void
	{
		AccessControl::log_as_id(0);

		$this->albums_tests->set_description('-1', 'new description', 422);
		$this->albums_tests->set_description('abcdefghijklmnopqrstuvwx', 'new description', 404);
		$this->albums_tests->set_protection_policy('-1', true, true, false, false, true, true, 422);
		$this->albums_tests->set_protection_policy('abcdefghijklmnopqrstuvwx', true, true, false, false, true, true, 404);

		AccessControl::logout();
	}
}
