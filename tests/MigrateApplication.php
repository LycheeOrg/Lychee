<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests;

use Illuminate\Support\Facades\Artisan;

trait MigrateApplication
{
	/**
	 * Make sure we are on the latest version of db.
	 *
	 * @return void
	 */
	public function migrateApplication(): void
	{
		Artisan::call('migrate', ['--force' => true]);
		$output = Artisan::output();
		if (trim($output) !== 'INFO  Nothing to migrate.') {
			echo $output . PHP_EOL;
		}
	}
}

