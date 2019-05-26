<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    /**
     * Test album functions.
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
        Session::put('login', true);
        Session::put('UserID', 0);

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

        Session::put('login', true);
        Session::put('UserID', 0);
        /**
         * Let's try to get the info of the album we just created.
         */
        $response = $this->post('/api/Album::get', ['albumID' => $albumID, 'password' => '']);
        $response->assertOk();
        $response->assertSee($albumID);
        $response->assertSee('test_album');

        /**
         * Let's try to change the title of the album we just created.
         */
        $response = $this->post('/api/Album::setTitle', ['albumIDs' => $albumID, 'title' => 'NEW_TEST']);
        $response->assertOk();
        $response->assertSee('true');

        /**
         * Let's see if the title changed.
         */
        $response = $this->post('/api/Album::get', ['albumID' => $albumID]);
        $response->assertOk();
        $response->assertSee($albumID);
        $response->assertDontSee('test_album');
        $response->assertSee('NEW_TEST');

        /**
         * Let's change the description of the album we just created.
         */
        $response = $this->post('/api/Album::setDescription', ['albumID' => $albumID, 'description' => 'new description']);
        $response->assertOk();
        $response->assertSee('true');

        /**
         * Let's see if the description changed.
         */
        $response = $this->post('/api/Album::get', ['albumID' => $albumID]);
        $response->assertOk();
        $response->assertSee($albumID);
        $response->assertDontSee('test_album');
        $response->assertSee('NEW_TEST');
        $response->assertSee('new description');

        /*
         * Flush the session to see if we can access the album
         */
        Session::flush();
        $response = $this->post('/api/Album::get', ['albumID' => $albumID]);
        $response->assertOk();
        $response->assertSee('"Warning: Album private!"');

        /*
         * Because we don't know login and password we are just going to assumed we are logged in.
         */
        Session::put('login', true);
        Session::put('UserID', 0);

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
