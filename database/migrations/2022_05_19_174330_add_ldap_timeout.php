<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLdapTimeout extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('configs', function (Blueprint $table) {
			DB::table('configs')->insert([[
				'key' => 'ldap_timeout',
				'value' => '1',
				'cat' => 'LDAP',
				'type_range' => 'int',
				'confidentiality' => '0',
				'description' => 'LDAP connection timeout',
			]]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::query()->where('key', '=', 'ldap_timeout')->delete();
	}
}
