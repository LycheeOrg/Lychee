<?php

namespace App\Console\Commands;

use App\LDAP\LDAPActions;
use App\Models\Configs;
use Illuminate\Console\Command;

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
