<?php

namespace Tests\Feature;

use App\Configs;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IndexTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_php_info()
    {

    	// we don't want a non admin to access this
	    $response = $this->get('/phpinfo');
	    $response->assertStatus(200);
	    $response->assertDontSeeText('php');
	    $response->assertSeeText('false');

    }

    public function test_landing_page()
    {
	    $landing_on_off = Configs::get_value('landing_page_enable', '0');
		Configs::set('landing_page_enable', 1);

	    $response = $this->get('/');
	    $response->assertStatus(200);
	    $response->assertViewIs('landing');

	    $response = $this->get('/gallery');
	    $response->assertStatus(200);
	    $response->assertViewIs('gallery');

	    Configs::set('landing_page_enable', $landing_on_off);

    }

}
