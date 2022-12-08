<?php

/** @noinspection PhpUndefinedClassInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UnhideConfigs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')->where('key', 'SL_enable')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'SL_for_admin')->update(['confidentiality' => '2']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', 'SL_enable')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'SL_for_admin')->update(['confidentiality' => '0']);
	}
}
