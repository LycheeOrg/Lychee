<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Support\Facades\Config;

/**
 * We disable migrate:reset command on any environment which are not dev.
 */
class MigrateResetCommand extends ResetCommand
{
	/**
	 * Create a new migration command instance.
	 * See: https://stackoverflow.com/questions/45938615/custom-laravel-migration-command-illuminate-database-migrations-migrationrepos.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct(app('migrator'));
	}

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '[Disabled when app.env != dev] Rollback all database migrations';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		if (Config::get('app.env') !== 'dev') {
			$this->warn("It's inadvisable to run migrate:reset on this project!");

			return Command::FAILURE;
		}

		return parent::handle();
	}
}
