<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use AccessControl;
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
		$albums_tests->add('0', 'test_album', 'false');

		$albums_tests->get('recent', '', 'true');
		$albums_tests->get('starred', '', 'true');
		$albums_tests->get('public', '', 'true');
		$albums_tests->get('unsorted', '', 'true');
	}

	public function testAddReadLogged()
	{
		$albums_tests = new AlbumsUnitTest($this);
		$session_tests = new SessionUnitTest();

		AccessControl::log_as_id(0);

		$albums_tests->get('recent', '', 'true');
		$albums_tests->get('starred', '', 'true');
		$albums_tests->get('public', '', 'true');
		$albums_tests->get('unsorted', '', 'true');

		$albumID = $albums_tests->add('0', 'test_album', 'true');
		$albumID2 = $albums_tests->add('0', 'test_album2', 'true');
		$albumID3 = $albums_tests->add('0', 'test_album3', 'true');
		$albumTagID1 = $albums_tests->addByTags('test_tag_album1', 'test', 'true');

		$albums_tests->set_tags($albumTagID1, 'test, coolnewtag, secondnewtag', 'true');
		$response = $albums_tests->get($albumTagID1, '', 'true');
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
		$albums_tests->get('999', '', 'false');

		$response = $albums_tests->get($albumID, '', 'true');
		$response->assertJson([
			'id' => $albumID,
			'description' => '',
			'title' => 'test_album',
			'albums' => [['id' => $albumID2]],
		]);

		$albums_tests->set_title($albumID, 'NEW_TEST');
		$albums_tests->set_description($albumID, 'new description');
		$albums_tests->set_license($albumID, 'WTFPL', '"Error: License not recognised!');
		$albums_tests->set_license($albumID, 'reserved');
		$albums_tests->set_sorting($albumID, 'title', 'ASC');

		/**
		 * Let's see if the info changed.
		 */
		$response = $albums_tests->get($albumID, '', 'true');
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
		$albums_tests->get_public($albumID, '', 'false');
		$albums_tests->get($albumID, '', '"Warning: Album private!"');

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

		$albums_tests->set_description('-1', 'new description', 'false');
		$albums_tests->set_public('-1', 1, 1, 1, 0, 1, 1, 'false');

		$session_tests->logout($this);
	}
}
