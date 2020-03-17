<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;

class UnhideConfigs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Configs::where('key', 'SL_enable')->exists()) {
			Configs::where('key', 'SL_enable')->update(['confidentiality' => '2']);
		}
		if (Configs::where('key', 'SL_for_admin')->exists()) {
			Configs::where('key', 'SL_for_admin')->update(['confidentiality' => '2']);
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
			if (Configs::where('key', 'SL_enable')->exists()) {
				Configs::where('key', 'SL_enable')->update(['confidentiality' => '0']);
			}
			if (Configs::where('key', 'SL_for_admin')->exists()) {
				Configs::where('key', 'SL_for_admin')->update(['confidentiality' => '0']);
			}
		}
	}
}
