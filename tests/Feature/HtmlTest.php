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
    public function testHtml()
    {
		// check if we can actually get a nice answer
	    $response = $this->get('/');
	    $response->assertOk();

	    // cache config
	    $configs = Configs::get(false);

	    // only check if that is unset
	    if($configs['password'] == '' && $configs['username'] == '')
	    {
		    $response = $this->post('/api/Settings::setLogin', ['function'=> 'setLogin', 'username' => 'lychee', 'password' => 'password']);
		    $response->assertSee("true");

		    $response = $this->post('/api/Session::logout');
		    $response->assertSee("true");

		    $response = $this->post('/api/Session::login', ['function'=> 'login', 'user' => 'foo', 'password' => 'bar']);
		    $response->assertSee("false");

		    $response = $this->post('/api/Session::login', ['function'=> 'login', 'user' => 'lychee', 'password' => 'bar']);
		    $response->assertSee("false");

		    $response = $this->post('/api/Session::login', ['function'=> 'login', 'user' => 'foo', 'password' => 'password']);
		    $response->assertSee("false");

		    $response = $this->post('/api/Session::login', ['function'=> 'login', 'user' => 'lychee', 'password' => 'password']);
		    $response->assertSee("true");

		    Configs::set('username', '');
		    Configs::set('password', '');
	    }
    }
}
