<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v1;

use App\Models\Configs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use function Safe\file_get_contents;
use Tests\AbstractTestCase;

class InstallTest extends AbstractTestCase
{
	private bool $configVueJs;

	public function before(): void
	{
		$this->configVueJs = config('feature.vuejs');
		config(['feature.vuejs' => false]);
	}

	public function after(): void
	{
		config(['feature.vuejs' => $this->configVueJs]);
	}

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

		$prevAppKey = config('app.key');
		config(['app.key' => null]);
		$response = $this->get('install/');
		$this->assertOk($response);
		config(['app.key' => $prevAppKey]);

		$response = $this->get('/');
		$this->assertOk($response);

		/*
		 * Clearing things up. We could do an Artisan migrate:reset but this is more efficient.
		 */
		Schema::disableForeignKeyConstraints();
		Schema::dropAllTables();
		Schema::enableForeignKeyConstraints();

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

		$response = $this->get('install/admin');
		$this->assertOk($response);
		$response->assertViewIs('install.setup-admin');

		/**
		 * set up admin user migration.
		 */
		$response = $this->post('install/admin', ['username' => 'admin', 'password' => 'password', 'password_confirmation' => 'password']);
		$this->assertOk($response);
		$response->assertViewIs('install.setup-success');

		// try to login with newly created admin
		self::assertTrue(Auth::attempt(['username' => 'admin', 'password' => 'password']));
		Auth::logout();

		/**
		 * Re-Installation should be forbidden now.
		 */
		$response = $this->get('install/');
		$this->assertForbidden($response);

		/**
		 * Setting admin should be forbidden now.
		 */
		$response = $this->get('install/admin');
		$this->assertForbidden($response);

		/**
		 * We now should NOT be redirected.
		 */
		Configs::invalidateCache();
		$response = $this->get('/');
		$this->assertOk($response);

		/*
		 * make sure there's still an admin user with ID 1
		 */
		/** @var User|null $admin */
		$admin = User::find(1);
		if ($admin === null) {
			$admin = new User();
			$admin->incrementing = false;
			$admin->id = 1;
			$admin->may_upload = true;
			$admin->may_edit_own_settings = true;
			$admin->may_administrate = true;
			$admin->username = 'admin';
			$admin->password = Hash::make('password');
			$admin->save();
			if (Schema::connection(null)->getConnection()->getDriverName() === 'pgsql' && DB::table('users')->count() > 0) {
				// when using PostgreSQL, the next ID value is kept when inserting without incrementing
				// which results in errors because trying to insert a user with ID = 1.
				// Thus, we need to reset the index to the greatest ID + 1
				/** @var User $lastUser */
				$lastUser = User::query()->orderByDesc('id')->first();
				DB::statement('ALTER SEQUENCE users_id_seq1 RESTART WITH ' . strval($lastUser->id + 1));
			}
		} elseif (!$admin->may_administrate) {
			$admin->may_administrate = true;
			$admin->save();
		}
	}
}
