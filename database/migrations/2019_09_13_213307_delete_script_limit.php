<?php

use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class DeleteScriptLimit extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			Configs::where('key', '=', 'php_script_no_limit')->delete();
			// Old name from v3.
			Configs::where('key', '=', 'php_script_limit')->delete();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'php_script_no_limit',
					'value' => '0',
					'confidentiality' => 3,
					'type_range' => BOOL,
				],
			]);
		}
	}
}
