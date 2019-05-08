<?php
/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class DiagnosticsTest extends TestCase
{
	/**
	 * A basic feature test example.
	 *
	 * @return void
	 */
	public function testExample()
	{
		// set user as admin
		Session::put('login', true);
		Session::put('UserID', 0);

		$response = $this->get('/Diagnostics');
		$response->assertStatus(200); // code 200 something

		$response = $this->post('/api/Diagnostics');
		$response->assertStatus(200); // code 200 something too

		Session::flush();
	}
}
