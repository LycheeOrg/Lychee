<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use App\Logs;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LogsTest extends TestCase
{
    /**
     * Test log handling.
     */
    public function test_Logs()
    {
        $response = $this->get('/Logs');
        $response->assertStatus(200); // code 200 something
        $response->assertSeeText('false');

        // set user as admin
        Session::put('login', true);
        Session::put('UserID', 0);

        $response = $this->get('/Logs');
        $response->assertStatus(200); // code 200 something
        $response->assertDontSeeText('false');
        if (Logs::count() == 0) {
            $response->assertSeeText('Everything looks fine, Lychee has not reported any problems!');
        } else {
            $response->assertViewIs('logs.list');
        }

        Session::flush();
    }

    public function test_api_Logs()
    {
        $response = $this->post('/api/Logs');
        $response->assertStatus(200); // code 200 something

		// we may decide to change for another out there so
    }

    public function test_clear_Logs()
    {
        $response = $this->post('/api/Logs::clearNoise');
        $response->assertStatus(200); // code 200 something
        $response->assertSeeText('false');

        $response = $this->post('/api/Logs::clear');
        $response->assertStatus(200); // code 200 something
        $response->assertSeeText('false');

        // set user as admin
        Session::put('login', true);
        Session::put('UserID', 0);

        $response = $this->post('/api/Logs::clearNoise');
        $response->assertStatus(200); // code 200 something
        $response->assertSeeText('Log Noise cleared');

        $response = $this->post('/api/Logs::clear');
        $response->assertStatus(200); // code 200 something
        $response->assertSeeText('Log cleared');

        Session::flush();
    }
}
