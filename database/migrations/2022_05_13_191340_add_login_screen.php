<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class AddLoginScreen extends Migration
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
				'key' => 'login_page_enable',
				'value' => '0',
				'cat' => 'Mod Welcome',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'Enable optional login screen',
			],
			[
				'key' => 'login_background',
				'value' => '',
				'cat' => 'Mod Welcome',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'Login screen background',
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
		Configs::where('key', '=', 'login_page_enable')->delete();
		Configs::where('key', '=', 'login_background')->delete();
	}
}
