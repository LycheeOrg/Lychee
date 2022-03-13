<?php

namespace Tests\Feature;

use App\Facades\AccessControl;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class AlbumTest extends TestCase
{
	/**
	 * Test album functions.
	 *
	 * @return void
	 */
	public function testAddNotLogged()
	{
		$albums_tests = new AlbumsUnitTest($this);
		$albums_tests->add(null, 'test_album', 401);

		$albums_tests->get('recent', 401);
		$albums_tests->get('starred', 401);
		$albums_tests->get('public', 401);
		$albums_tests->get('unsorted', 401);

		// Ensure that we get proper 404 (not found) response for a
		// non-existing album, not a false 403 (forbidden) response
		$albums_tests->get('abcdefghijklmnopqrstuvwx', 404);
	}

	public function testAddReadLogged()
	{
		$albums_tests = new AlbumsUnitTest($this);
		$session_tests = new SessionUnitTest($this);

		AccessControl::log_as_id(0);

		$albums_tests->get('recent');
		$albums_tests->get('starred');
		$albums_tests->get('public');
		$albums_tests->get('unsorted');

		$albumID = $albums_tests->add(null, 'test_album')->offsetGet('id');
		$albumID2 = $albums_tests->add(null, 'test_album2')->offsetGet('id');
		$albumID3 = $albums_tests->add(null, 'test_album3')->offsetGet('id');
		$albumTagID1 = $albums_tests->addByTags('test_tag_album1', ['test'])->offsetGet('id');

		$albums_tests->set_tags($albumTagID1, ['test', 'coolnewtag', 'secondnewtag']);
		$response = $albums_tests->get($albumTagID1);
		$response->assertJson([
			'show_tags' => ['test', 'coolnewtag', 'secondnewtag'],
		]);

		$albums_tests->see_in_albums($albumID);
		$albums_tests->see_in_albums($albumID2);
		$albums_tests->see_in_albums($albumID3);
		$albums_tests->see_in_albums($albumTagID1);

		$albums_tests->move([$albumTagID1], $albumID3, 404);
		$albums_tests->move([$albumID3], $albumID2);
		$albums_tests->move([$albumID2], $albumID);
		$albums_tests->move([$albumID3], null);

		/*
		 * try to get a non-existing album
		 */
		$albums_tests->get('abcdefghijklmnopqrstuvwx', 404);

		$response = $albums_tests->get($albumID);
		$response->assertJson([
			'id' => $albumID,
			'description' => null,
			'title' => 'test_album',
			'albums' => [['id' => $albumID2]],
		]);

		$albums_tests->set_title($albumID, 'NEW_TEST');
		$albums_tests->set_description($albumID, 'new description');
		$albums_tests->set_license($albumID, 'WTFPL', 422);
		$albums_tests->set_license($albumID, 'reserved');
		$albums_tests->set_sorting($albumID, 'title', 'ASC');

		/**
		 * Let's see if the info changed.
		 */
		$response = $albums_tests->get($albumID);
		$response->assertJson([
			'id' => $albumID,
			'description' => 'new description',
			'title' => 'NEW_TEST',
		]);

		$albums_tests->set_sorting($albumID, '', 'ASC');

		/*
		 * Flush the session to see if we can access the album
		 */
		$session_tests->logout();

		/*
		 * Let's try to get the info of the album we just created.
		 */
		$albums_tests->unlock($albumID, '', 422);
		$albums_tests->unlock($albumID, 'wrong-password', 403);
		$albums_tests->get($albumID, 401);

		/*
		 * Because we don't know login and password we are just going to assumed we are logged in.
		 */
		AccessControl::log_as_id(0);

		/*
		 * Let's try to delete this album.
		 */
		$albums_tests->delete([$albumID]);

		/*
		 * Because we deleted the album, we should not see it anymore.
		 */
		$albums_tests->dont_see_in_albums($albumID);

		$session_tests->logout();
	}

	public function testTrueNegative()
	{
		$albums_tests = new AlbumsUnitTest($this);
		$session_tests = new SessionUnitTest($this);

		AccessControl::log_as_id(0);

		$albums_tests->set_description('-1', 'new description', 422);
		$albums_tests->set_description('abcdefghijklmnopqrstuvwx', 'new description', 404);
		$albums_tests->set_protection_policy('-1', true, true, false, false, true, true, 422);
		$albums_tests->set_protection_policy('abcdefghijklmnopqrstuvwx', true, true, false, false, true, true, 404);

		$session_tests->logout();
	}
}
