<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

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
	public function test_add_not_logged()
	{
		$albums_tests = new AlbumsUnitTest();
		$albums_tests->add($this, '0', 'test_album', 'false');

		$albums_tests->get($this, 'r', '', 'true');
		$albums_tests->get($this, 's', '', 'true');
		$albums_tests->get($this, 'f', '', 'true');
		$albums_tests->get($this, '0', '', 'true');
	}

	public function test_add_read_logged()
	{
		$albums_tests = new AlbumsUnitTest();
		$session_tests = new SessionUnitTest();

		$session_tests->log_as_id(0);

		$albums_tests->get($this, 'r', '', 'true');
		$albums_tests->get($this, 's', '', 'true');
		$albums_tests->get($this, 'f', '', 'true');
		$albums_tests->get($this, '0', '', 'true');

		$albumID = $albums_tests->add($this, '0', 'test_album', 'true');
		$albums_tests->see_in_albums($this, $albumID);

		/*
		 * try to get a non existing album
		 */
		$albums_tests->get($this, '999', '', 'false');

		$response = $albums_tests->get($this, $albumID, '', 'true');
		$response->assertJson([
			'id' => $albumID,
			'description' => '',
			'title' => 'test_album',
		]);

		$albums_tests->set_title($this, $albumID, 'NEW_TEST');
		$albums_tests->set_description($this, $albumID, 'new description');
		$albums_tests->set_license($this, $albumID, 'WTFPL', '"Error: wrong kind of license!"');
		$albums_tests->set_license($this, $albumID, 'reserved');

		/**
		 * Let's see if the info changed.
		 */
		$response = $albums_tests->get($this, $albumID, '', 'true');
		$response->assertJson([
			'id' => $albumID,
			'description' => 'new description',
			'title' => 'NEW_TEST',
		]);

		/*
		 * Flush the session to see if we can access the album
		 */
		$session_tests->logout($this);

		/*
		 * Let's try to get the info of the album we just created.
		 */
		$albums_tests->get_public($this, $albumID, '', 'false');
		$albums_tests->get($this, $albumID, '', '"Warning: Album private!"');

		/*
		 * Because we don't know login and password we are just going to assumed we are logged in.
		 */
		$session_tests->log_as_id(0);

		/*
		 * Let's try to delete this album.
		 */
		$albums_tests->delete($this, $albumID);

		/*
		 * Because we deleted the album, we should not see it anymore.
		 */
		$albums_tests->dont_see_in_albums($this, $albumID);

		$session_tests->logout($this);
	}

	public function test_true_negative()
	{
		$albums_tests = new AlbumsUnitTest();
		$session_tests = new SessionUnitTest();

		$session_tests->log_as_id(0);

		$albums_tests->set_description($this, '-1', 'new description', 'false');
		$albums_tests->set_public($this, '-1', 1, 1, 1, 1, 1, 'false');

		$session_tests->logout($this);
	}
}
