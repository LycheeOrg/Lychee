<?php

namespace Tests\Feature;

use App\Configs;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class UpdateTest extends TestCase
{
	private function do_call($result)
	{
		$response = $this->post('/api/Update::Apply', []);
		$response->assertOk();
		$response->assertSee($result);
	}

	public function test_do_not_logged()
	{
		$response = $this->get('/Update', []);
		$response->assertOk();
		$response->assertSee('false');

		$response = $this->post('/api/Update::Apply', []);
		$response->assertOk();
		$response->assertSee('false');

		$response = $this->post('/api/Update::Check', []);
		$response->assertOk();
		$response->assertSee('false');
	}

	public function test_do_logged()
	{
		$gitpull = Configs::get_value('allow_online_git_pull', '0');

		$session_tests = new SessionUnitTest();
		$session_tests->log_as_id(0);

		Configs::set('allow_online_git_pull', '0');
		$this->do_call('Error: Online updates are not allowed.');

		Configs::set('allow_online_git_pull', '1');

		$response = $this->get('/Update', []);
		$response->assertOk();

		$response = $this->post('/api/Update::Apply', []);
		$response->assertOk();

		$response = $this->post('/api/Update::Check', []);
		$response->assertOk();

		Configs::set('allow_online_git_pull', $gitpull);

		$session_tests->logout($this);
	}
}