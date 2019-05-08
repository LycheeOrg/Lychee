<?php
/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use App\Logs;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class DiagnosticsTest extends TestCase
{
	/**
	 * Test diagnostics
	 *
	 * @return void
	 */
	public function test_diagnostics()
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
