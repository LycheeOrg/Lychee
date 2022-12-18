<?php

namespace App\Console\Commands;

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
		$this->line('Command deprecated. Instead, use php artisan lychee:update_user or lychee:create_user --may_administrate.');

		return 1;
	}
}