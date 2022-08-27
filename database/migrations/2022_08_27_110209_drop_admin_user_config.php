<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class DropAdminUserConfig extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::whereIn('key', ['username', 'password'])->delete();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		defined('STRING_REQ') or define('STRING_REQ', 'string_required');

		Configs::insert(
			[
				'key' => 'username',
				'value' => '',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
				'confidentiality' => '4',
			],
			[
				'key' => 'password',
				'value' => '',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
				'confidentiality' => '4',
			]
		);
	}
}
