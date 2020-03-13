<?php

use App\Configs;
use App\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddForce32BitIds extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');

		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'force_32bit_ids',
					'value' => '0',
					'cat' => 'config',
					'type_range' => BOOL,
					'confidentiality' => '0',
				],
			]);
		} else {
			Logs::warning(__METHOD__, __LINE__, 'Table configs does not exist');
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
			Configs::where('key', '=', 'force_32bit_ids')->delete();
		}
	}
}
