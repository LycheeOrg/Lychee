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
		$album_tests = new AlbumsUnitTest();
		$album_tests->add($this, '0', 'test_album', 'false');
	}

	public function test_add_read_logged()
	{
		$album_tests = new AlbumsUnitTest();
		$session_tests = new SessionUnitTest();

		$session_tests->log_as_id(0);

		$albumID = $album_tests->add($this, '0', 'test_album', 'true');
		$album_tests->see_in_albums($this, $albumID);

		/*
		 * try to get a non existing album
		 */
		$album_tests->get($this, '999', '', 'false');

		$response = $album_tests->get($this, $albumID, '', 'true');
		$response->assertJson([
			'id' => $albumID,
			'description' => '',
			'title' => 'test_album',
		]);

		$album_tests->set_title($this, $albumID, 'NEW_TEST');
		$album_tests->set_description($this, $albumID, 'new description');
		$album_tests->set_license($this, $albumID, 'reserved');

		/**
		 * Let's see if the info changed.
		 */
		$response = $album_tests->get($this, $albumID, '', 'true');
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
		$album_tests->get_public($this, $albumID, '', 'false');
		$album_tests->get($this, $albumID, '', '"Warning: Album private!"');

		/*
		 * Because we don't know login and password we are just going to assumed we are logged in.
		 */
		$session_tests->log_as_id(0);

		/**
		 * Let's try to delete this album.
		 */
		$response = $this->post('/api/Album::delete', ['albumIDs' => $albumID]);
		$response->assertOk();
		$response->assertSee('true');

		/**
		 * Because we deleted the album, we should not see it anymore.
		 */
		$response = $this->post('/api/Albums::get', []);
		$response->assertOk();
		$response->assertDontSee($albumID);

		$session_tests->logout($this);
	}
}
