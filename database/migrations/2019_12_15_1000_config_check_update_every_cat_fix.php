<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigCheckUpdateEveryCatFix extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Configs::where('key', 'update_check_every_days')->exists()) {
			Configs::where('key', 'update_check_every_days')->update(['cat' => 'config']);
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
			Configs::where('key', 'update_check_every_days')->update(['cat' => 'Config']);
		}
	}
}
