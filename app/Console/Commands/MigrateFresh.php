<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Support\Facades\Config;

/**
 * We disable migrate:fresh command on any environment which are not dev.
 */
class MigrateFresh extends FreshCommand
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
			$this->warn("It's inadvisable to run migrate:fresh on this project!");

			return Command::FAILURE;
		}

		return parent::handle();
	}
}
