<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

class UpdateTest extends TestCase
{
	public function testDoNotLogged(): void
	{
		$response = $this->get('/Update', []);
		$response->assertForbidden();

		$response = $this->postJson('/api/Update::apply');
		$response->assertForbidden();

		$response = $this->postJson('/api/Update::check');
		$response->assertForbidden();
	}

	public function testDoLogged(): void
	{
		$gitpull = Configs::get_value('allow_online_git_pull', '0');

		AccessControl::log_as_id(0);

		Configs::set('allow_online_git_pull', '0');
		$response = $this->postJson('/api/Update::apply');
		$response->assertStatus(412);
		$response->assertSee('Online updates are disabled by configuration');

		Configs::set('allow_online_git_pull', '1');

		$response = $this->get('/Update', []);
		$response->assertOk();

		$response = $this->postJson('/api/Update::apply');
		$response->assertOk();

		$response = $this->postJson('/api/Update::check');
		if ($response->status() === 500) {
			// We need an OR-condition here.
			// If we are inside the Lychee repository but on a development
			// branch which is not the master branch, then we get the first
			// error message.
			// If we are _not_ inside the Lychee repository (e.g. we are
			// testing a PR from a 3rd-party contributor), then we get the
			// second error message.
			try {
				$response->assertSee('Branch is not master, cannot compare');
			} catch (ExpectationFailedException) {
				$response->assertSee('Could not determine the branch');
			}
		} else {
			$response->assertOk();
		}

		Configs::set('allow_online_git_pull', $gitpull);

		AccessControl::logout();
	}
}
