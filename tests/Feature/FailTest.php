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
		$response->assertStatus(false); // code 200 something

		Session::flush();

	}

}
