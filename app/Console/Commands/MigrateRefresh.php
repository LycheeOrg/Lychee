<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Support\Facades\Config;

/**
 * We disable migrate:refresh command on any environment which are not dev.
 */
class MigrateRefresh extends RefreshCommand
{
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '[Disabled when app.env != dev] Drop all tables and re-run all migrations';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		if (Config::get('app.env') !== 'dev') {
			$this->warn("It's inadvisable to run migrate:refresh on this project!");

			return Command::FAILURE;
		}

		return parent::handle();
	}
}
