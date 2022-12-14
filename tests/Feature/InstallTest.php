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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstallTest extends TestCase
{
	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testInstall(): void
	{
		/*
		 * Get previous config
		 */
		/** @var User $admin */
		$admin = User::query()->find(0);

		$prevAppKey = config('app.key');
		config(['app.key' => null]);
		$response = $this->get('install/');
		$this->assertOk($response);
		config(['app.key' => $prevAppKey]);

		$response = $this->get('/');
		$this->assertOk($response);

		/*
		 * Clearing things up. We could do an Artisan migrate but this is more efficient.
		 */

		// The order is important: referring tables must be deleted first, referred tables last
		$tables = [
			'sym_links',
			'size_variants',
			'photos',
			'configs',
			'logs',
			'migrations',
			'notifications',
			'page_contents',
			'pages',
			'user_base_album',
			'tag_albums',
			'albums',
			'base_albums',
			'webauthn_credentials',
			'users',
		];

		if (Schema::connection(null)->getConnection()->getDriverName() !== 'sqlite') {
			// We must remove the foreign constraint from `albums` to `photos` to
			// break up circular dependencies.
			Schema::table('albums', function (Blueprint $table) {
				$table->dropForeign('albums_cover_id_foreign');
			});
		}

		foreach ($tables as $table) {
			Schema::dropIfExists($table);
		}

		/**
		 * No database: we should be redirected to install: default case.
		 */
		$response = $this->get('/');
		$this->assertStatus($response, 307);
		$response->assertRedirect('install/');

		/**
		 * Check the welcome page.
		 */
		$response = $this->get('install/');
		$this->assertOk($response);
		$response->assertViewIs('install.welcome');

		/**
		 * Check the requirements page.
		 */
		$response = $this->get('install/req');
		$this->assertOk($response);
		$response->assertViewIs('install.requirements');

		/**
		 * Check the permissions page.
		 */
		$response = $this->get('install/perm');
		$this->assertOk($response);
		$response->assertViewIs('install.permissions');

		/**
		 * Check the env page.
		 */
		$response = $this->get('install/env');
		$this->assertOk($response);
		$response->assertViewIs('install.env');

		$env = file_get_contents(base_path('.env'));

		/**
		 * POST '.env' the env page.
		 */
		$response = $this->post('install/env', ['envConfig' => $env]);
		$this->assertOk($response);
		$response->assertViewIs('install.env');

		/**
		 * apply migration.
		 */
		$response = $this->get('install/migrate');
		$this->assertOk($response);
		$response->assertViewIs('install.migrate');

		/**
		 * Re-Installation should be forbidden now.
		 */
		$response = $this->get('install/');
		$this->assertForbidden($response);

		/**
		 * We now should NOT be redirected.
		 */
		Configs::invalidateCache();
		$response = $this->get('/');
		$this->assertOk($response);

		$admin->save();
		$admin->id = 0;
		$admin->save();
	}
}
