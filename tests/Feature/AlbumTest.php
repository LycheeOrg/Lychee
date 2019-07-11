<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use App\ModelFunctions\SessionFunctions;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class AlbumTest extends TestCase
{
	/**
	 * Pack the possible response of test.
	 *
	 * @param $response
	 * @param $should_fail
	 */
	private function fail_test(TestResponse &$response, bool &$should_fail)
	{
		if ($should_fail) {
			$response->assertSee('false');
		} else {
			$response->assertSee('true');
		}
	}

	/**
	 * Pack the title tests.
	 *
	 * @param $id
	 * @param $text
	 * @param $should_fail
	 */
	private function set_title($id, string $text, bool $should_fail)
	{
		/**
		 * Let's try to change the title of the album we just created.
		 */
		$response = $this->post('/api/Album::setTitle', ['albumIDs' => $id, 'title' => $text]);
		$response->assertOk();
		$this->fail_test($response, $should_fail);
	}

	/**
	 * Pack the description tests.
	 *
	 * @param $id
	 * @param $text
	 * @param $should_fail
	 */
	private function set_description($id, string $text, bool $should_fail)
	{
		$response = $this->post('/api/Album::setDescription', ['albumID' => $id, 'description' => $text]);
		$response->assertOk();
		$this->fail_test($response, $should_fail);
	}

	/**
	 * Pack the licence.
	 *
	 * @param $id
	 * @param $text
	 * @param $should_fail
	 */
	private function set_licence($id, string $text, bool $should_fail)
	{
		$response = $this->post('/api/Album::setLicense', ['albumID' => $id, 'license' => $text]);
		$response->assertOk();
		$this->fail_test($response, $should_fail);
	}

	/**
	 * Test album functions.
	 *
	 * @return void
	 */
	public function test_add_not_logged()
	{
		/**
		 * We are not logged in so this should fail.
		 */
		$response = $this->post('/api/Album::add', [
			'title' => 'test_album',
			'parent_id' => '0',
		]);
		$response->assertOk();
		$response->assertSee('false');
	}

	public function test_add_read_logged()
	{
		/*
		 * Because we don't know login and password we are just going to assumed we are logged in.
		 */
		$sessionFunctions = new SessionFunctions();
		$sessionFunctions->log_as_id(0);

		/**
		 * We are logged as ADMIN (we don't test the other users yet) so this should not fail and it should return an id.
		 */
		$response = $this->post('/api/Album::add', [
			'title' => 'test_album',
			'parent_id' => '0',
		]);
		$response->assertOk();
		$response->assertDontSee('false');

		/**
		 * We also get the id of the album we just created.
		 */
		$albumID = $response->getContent();

		/**
		 * Let's get all current albums.
		 */
		$response = $this->post('/api/Albums::get', []);
		$response->assertOk();
		$response->assertSee($albumID);

		/**
		 * Let's try to get a non existing album.
		 */
		$response = $this->post('/api/Album::get', ['albumID' => 999, 'password' => '']);
		$response->assertOk();
		$response->assertSeeText('false');

		/**
		 * Let's try to get the info of the album we just created.
		 */
		$response = $this->post('/api/Album::get', ['albumID' => $albumID, 'password' => '']);
		$response->assertOk();
		$response->assertSee($albumID);
		$response->assertJson([
			'id' => $albumID,
			'description' => '',
			'title' => 'test_album',
		]);

		/*
		 * Let's try to change the title of the album we just created.
		 */
		$this->set_title($albumID, 'NEW_TEST', false);
		//        $this->set_title(9999,'NEW_TEST',true);

		/*
		 * Let's change the description of the album we just created.
		 */
		$this->set_description($albumID, 'new description', false);
		//        $this->set_description(9999,'new description', true);

		/*
		 * Let's change the licence used
		 */
		$this->set_licence($albumID, 'reserved', false);

		/**
		 * Let's see if the info changed.
		 */
		$response = $this->post('/api/Album::get', ['albumID' => $albumID, 'password' => '']);
		$response->assertOk();
		$response->assertSee($albumID);
		$response->assertJson([
			'id' => $albumID,
			'description' => 'new description',
			'title' => 'NEW_TEST',
		]);

		/*
		 * Flush the session to see if we can access the album
		 */
		$sessionFunctions->logout();

		/**
		 * Let's try to get the info of the album we just created.
		 */
		$response = $this->post('/api/Album::getPublic', ['albumID' => $albumID, 'password' => '']);
		$response->assertOk();
		$response->assertSeeText('false');

		$response = $this->post('/api/Album::get', ['albumID' => $albumID]);
		$response->assertOk();
		$response->assertSee('"Warning: Album private!"');

		/*
		 * Because we don't know login and password we are just going to assumed we are logged in.
		 */
		$sessionFunctions->log_as_id(0);

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
	}
}
