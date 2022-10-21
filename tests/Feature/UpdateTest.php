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

use App\Models\Configs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Feature\Traits\CatchFailures;
use Tests\TestCase;

class UpdateTest extends TestCase
{
	use CatchFailures;

	public function testDoNotLogged(): void
	{
		$response = $this->get('/Update', []);
		$this->assertUnauthorized($response);

		$response = $this->postJson('/api/Update::apply');
		$this->assertUnauthorized($response);

		$response = $this->postJson('/api/Update::check');
		$this->assertUnauthorized($response);
	}

	public function testDoLogged(): void
	{
		$gitpull = Configs::getValue('allow_online_git_pull', '0');

		Auth::loginUsingId(0);

		Configs::set('allow_online_git_pull', '0');
		$response = $this->postJson('/api/Update::apply');
		$this->assertStatus($response, 412);
		$response->assertSee('Online updates are disabled by configuration');

		Configs::set('allow_online_git_pull', '1');

		$response = $this->get('/Update', []);
		$this->assertOk($response);

		$response = $this->postJson('/api/Update::apply');
		$this->assertOk($response);

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
			$this->assertOk($response);
		}

		Configs::set('allow_online_git_pull', $gitpull);

		Auth::logout();
		Session::flush();
	}

	/**
	 * We check that we can apply migration.
	 * This requires us to disable the {@link \App\Http\Middleware\MigrationStatus} middleware otherwise
	 * we will be thrown out all the time.
	 */
	public function testApplyMigration()
	{
		// Prepare for test: we need to make sure there is an admin user registered.
		/** @var User $adminUser */
		$adminUser = User::findOrFail(0);
		$login = $adminUser->username;
		$pw = $adminUser->password;
		$adminUser->username = Hash::make('test_login');
		$adminUser->password = Hash::make('test_password');
		$adminUser->save();

		// We disable middlewares because they are not what we want to test here.
		$this->withoutMiddleware();

		// make sure we are logged out
		Auth::logout();
		Session::flush();
		$response = $this->postJson('/migrate');
		$this->assertForbidden($response);

		$response = $this->postJson('/migrate', ['username' => 'test_login', 'password' => 'test_password']);
		$this->assertOk($response);

		// check that Legacy did change the username
		$adminUser = User::findOrFail(0);
		$this->assertEquals('test_login', $adminUser->username);

		// clean up
		Auth::logout();
		Session::flush();
		$adminUser->username = $login;
		$adminUser->password = $pw;
		$adminUser->save();
	}
}
