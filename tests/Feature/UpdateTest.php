<?php

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use Tests\TestCase;

class UpdateTest extends TestCase
{
	public function testDoNotLogged()
	{
		$response = $this->get('/Update', []);
		$response->assertForbidden();

		$response = $this->postJson('/api/Update::apply', []);
		$response->assertForbidden();

		$response = $this->postJson('/api/Update::check', []);
		$response->assertForbidden();
	}

	public function testDoLogged()
	{
		$gitpull = Configs::get_value('allow_online_git_pull', '0');

		AccessControl::log_as_id(0);

		Configs::set('allow_online_git_pull', '0');
		$response = $this->postJson('/api/Update::apply', []);
		$response->assertStatus(412);
		$response->assertSee('Online updates are disabled by configuration');

		Configs::set('allow_online_git_pull', '1');

		$response = $this->get('/Update', []);
		$response->assertOk();

		$response = $this->postJson('/api/Update::apply', []);
		$response->assertOk();

		$response = $this->postJson('/api/Update::check', []);
		if ($response->status() === 500) {
			$response->assertSee('Branch is not master, cannot compare');
		} else {
			$response->assertOk();
		}

		Configs::set('allow_online_git_pull', $gitpull);

		AccessControl::logout();
	}
}
