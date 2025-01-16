<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\Legacy;

use Illuminate\Console\Command;

class ResetAdmin extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:reset_admin';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset Login and Password of the admin user (deprecated).';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$this->line('Command deprecated. Instead, use php artisan lychee:update_user or lychee:create_user {username} {password} --may-administrate.');

		return 1;
	}
}