<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class DiagnosticsTest extends TestCase
{
	/**
	 * Test diagnostics.
	 *
	 * @return void
	 */
	public function test_diagnostics()
	{
		$session_tests = new SessionUnitTest();
		$session_tests->log_as_id(0);

		$response = $this->get('/Diagnostics');
		$response->assertStatus(200); // code 200 something

		$response = $this->post('/api/Diagnostics');
		$response->assertStatus(200); // code 200 something too

		$session_tests->logout($this);
	}
}
