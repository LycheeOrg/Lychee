<?php /** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use App\Logs;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class FailTest extends TestCase
{
	/**
	 * Fail
	 *
	 * @return void
	 */
	public function fail_test()
	{
		$response = $this->get('/Logs');
		$response->assertStatus("rabbit"); // code 200 something
		$response->assertStatus(12345); // code 200 something

		Session::flush();

	}

}
