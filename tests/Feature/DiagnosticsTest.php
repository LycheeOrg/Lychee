<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use App\Models\Configs;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class DiagnosticsTest extends TestCase
{
	/**
	 * Test diagnostics.
	 *
	 * @return void
	 */
	public function testDiagnostics()
	{
		$response = $this->get('/Diagnostics');
		$response->assertStatus(200); // code 200 something

		$session_tests = new SessionUnitTest();
		$session_tests->log_as_id(0);

		$response = $this->get('/Diagnostics');
		$response->assertStatus(200); // code 200 something

		Configs::where('key', '=', 'lossless_optimization')->update(['value' => null]);

		$response = $this->post('/api/Diagnostics');
		$response->assertStatus(200); // code 200 something too

		$response = $this->post('/api/Diagnostics::getSize');
		$response->assertStatus(200); // code 200 something too

		Configs::where('key', '=', 'lossless_optimization')->update(['value' => '1']);

		$session_tests->logout($this);
	}
}
