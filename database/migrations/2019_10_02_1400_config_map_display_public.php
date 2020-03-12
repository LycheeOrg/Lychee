<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use App\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigMapDisplayPublic extends Migration
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
					'key' => 'map_display_public',
					'value' => '0',
					'confidentiality' => 0,
					'cat' => 'Mod Map',
					'type_range' => BOOL,
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
			Configs::where('key', '=', 'map_display_public')->delete();
		}
	}
}
