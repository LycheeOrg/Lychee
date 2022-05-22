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
