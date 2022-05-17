<?php

namespace App\Console\Commands;

use App\LDAP\LDAPActions;
use App\Models\Configs;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class LDAPUpdateAllUsers extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:LDAP_update_all_users';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Updates the users table from the LDAP server if LDAP is enabled';

	/**
	 * Create a new command instance.
	 *
	 * @throws SymfonyConsoleException
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		if (Configs::get_Value('ldap_enabled', '0') !== '1') {
			echo 'LDAP is not enabled!' . PHP_EOL;

			return 0;
		}
		LDAPActions::update_all_users();

		return 0;
	}
}
