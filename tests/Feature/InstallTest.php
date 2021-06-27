<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstallTest extends TestCase
{
	/**
	 * Testing the Login interface.
	 *
	 * @return void
	 */
	public function testInstall()
	{
		/*
		 * Get previous config
		 */
		$admin = User::find(0);

		touch(base_path('.NO_SECURE_KEY'));
		$response = $this->get('install/');
		$response->assertStatus(200);
		@unlink(base_path('.NO_SECURE_KEY'));

		@unlink(base_path('installed.log'));
		/**
		 * No installed.log: we should not be redirected to install (case where we have not done the last migration).
		 */
		$response = $this->get('/');
		$response->assertStatus(200);

		/*
		 * Clearing things up. We could do an Artisan migrate but this is more efficient.
		 */
		$tables = [
			'sym_links',
			'size_variants',
			'photos',
			'configs',
			'logs',
			'migrations',
			'page_contents',
			'pages',
			'user_album',
			'users',
			'albums',
			'web_authn_credentials',
		];
		foreach ($tables as $table) {
			Schema::dropIfExists($table);
		}

		/**
		 * No database: we should be redirected to install: default case.
		 */
		$response = $this->get('/');
		$response->assertStatus(307);
		$response->assertRedirect('install/');

		/**
		 * Check the welcome page.
		 */
		$response = $this->get('install/');
		$response->assertStatus(200);
		$response->assertViewIs('install.welcome');

		/**
		 * Check the requirements page.
		 */
		$response = $this->get('install/req');
		$response->assertStatus(200);
		$response->assertViewIs('install.requirements');

		/**
		 * Check the permissions page.
		 */
		$response = $this->get('install/perm');
		$response->assertStatus(200);
		$response->assertViewIs('install.permissions');

		/**
		 * Check the env page.
		 */
		$response = $this->get('install/env');
		$response->assertStatus(200);
		$response->assertViewIs('install.env');

		$env = file_get_contents(base_path('.env'));

		/**
		 * POST '.env' the env page.
		 */
		$response = $this->post('install/env', ['envConfig' => $env]);
		$response->assertStatus(200);
		$response->assertViewIs('install.env');

		/**
		 * apply migration.
		 */
		$response = $this->get('install/migrate');
		$response->assertStatus(200);
		$response->assertViewIs('install.migrate');

		/**
		 * We now should be redirected.
		 */
		$response = $this->get('install/');
		$response->assertStatus(307);
		$response->assertRedirect('/');

		/**
		 * We now should NOT be redirected.
		 */
		$response = $this->get('/');
		$response->assertStatus(200);

		$admin->save();
		$admin->id = 0;
		$admin->save();
	}
}
