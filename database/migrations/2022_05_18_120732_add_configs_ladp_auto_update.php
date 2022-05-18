<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddConfigsLadpAutoUpdate extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')->insert([
			[
				'key' => 'ldap_purge',
				'value' => '0',
				'cat' => 'LDAP',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'LDAP enables purging of obsolete users in lychee',
			],
			[
				'key' => 'ldap_update_users',
				'value' => '0',
				'cat' => 'LDAP',
				'type_range' => 'int',
				'confidentiality' => '0',
				'description' => 'LDAP schedule interval for automatic sync of users in minutes',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::query()->where('key', '=', 'ldap_purge')->delete();
		Configs::query()->where('key', '=', 'ldap_update_users')->delete();
	}
}
