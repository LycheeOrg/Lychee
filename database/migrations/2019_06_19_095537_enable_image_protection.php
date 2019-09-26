<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class EnableImageProtection extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'SL_enable',
					'value' => '0',
					'confidentiality' => 3,
					'cat' => 'Symbolic Link',
				],
				[
					'key' => 'SL_for_admin',
					'value' => '0',
					'confidentiality' => 3,
					'cat' => 'Symbolic Link',
				],
				[
					'key' => 'SL_life_time_days',
					'value' => '3',
					'confidentiality' => 3,
					'cat' => 'Symbolic Link',
				],
			]);
		} else {
			echo "Table configs does not exist\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Configs::where('key', '=', 'SL_enable')->delete();
			Configs::where('key', '=', 'SL_for_admin')->delete();
			Configs::where('key', '=', 'SL_life_time_days')->delete();
		}
	}
}
