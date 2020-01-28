<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ComposerUpdate extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!defined('BOOL')) {
			define('BOOL', '0|1');
		}

		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'apply_composer_update',
					'value' => '0',
					'confidentiality' => '3',
					'cat' => 'Admin',
					'type_range' => BOOL,
				],
			]);
		} else {
			echo "Table configs does not exists\n";
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
			Configs::where('key', '=', 'apply_composer_update')->delete();
		}
	}
}
