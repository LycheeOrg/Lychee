<?php

namespace Tests\Feature;

use App\Configs;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HtmlTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
		// check if we can actually get a nice answer
	    $response = $this->get('/');
	    $response->assertSuccessful();

	    $response = $this->post('/api/Settings::setLogin', ['function'=> 'setLogin', 'username' => 'lychee', 'password' => 'password']);
	    $response->assertSee("true");

    }
}
