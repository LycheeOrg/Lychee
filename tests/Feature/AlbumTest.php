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
		$albums_tests->add('0', 'test_album', 401);

		$albums_tests->get('recent', '', 403);
		$albums_tests->get('starred', '', 403);
		$albums_tests->get('public', '', 403);
		$albums_tests->get('unsorted', '', 403);
	}

	public function testAddReadLogged()
	{
		$albums_tests = new AlbumsUnitTest($this);
		$session_tests = new SessionUnitTest();

		AccessControl::log_as_id(0);

		$albums_tests->get('recent');
		$albums_tests->get('starred');
		$albums_tests->get('public');
		$albums_tests->get('unsorted');

		$albumID = $albums_tests->add('0', 'test_album');
		$albumID2 = $albums_tests->add('0', 'test_album2');
		$albumID3 = $albums_tests->add('0', 'test_album3');
		$albumTagID1 = $albums_tests->addByTags('test_tag_album1', 'test');

		$albums_tests->set_tags($albumTagID1, 'test, coolnewtag, secondnewtag');
		$response = $albums_tests->get($albumTagID1);
		$response->assertSee('test, coolnewtag, secondnewtag');

		$albums_tests->see_in_albums($albumID);
		$albums_tests->see_in_albums($albumID2);
		$albums_tests->see_in_albums($albumID3);
		$albums_tests->see_in_albums($albumTagID1);

		$albums_tests->move($albumTagID1, $albumID3);
		$albums_tests->move($albumID3, $albumID2);
		$albums_tests->move($albumID2, $albumID);
		$albums_tests->move($albumID3, '0');

		/*
		 * try to get a non existing album
		 */
		$albums_tests->get('999', '', 403);

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
		$session_tests->logout($this);

		/*
		 * Let's try to get the info of the album we just created.
		 */
		$albums_tests->get_public($albumID, '', 403);
		$albums_tests->get($albumID, '', 403);

		/*
		 * Because we don't know login and password we are just going to assumed we are logged in.
		 */
		AccessControl::log_as_id(0);

		/*
		 * Let's try to delete this album.
		 */
		$albums_tests->delete($albumID);

		/*
		 * Because we deleted the album, we should not see it anymore.
		 */
		$albums_tests->dont_see_in_albums($albumID);

		$session_tests->logout($this);
	}

	public function testTrueNegative()
	{
		$albums_tests = new AlbumsUnitTest($this);
		$session_tests = new SessionUnitTest();

		AccessControl::log_as_id(0);

		$albums_tests->set_description('-1', 'new description', 401);
		$albums_tests->set_public('-1', 1, 1, 1, 0, 1, 1, 401);

		$session_tests->logout($this);
	}
}
