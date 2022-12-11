<?php

/** @noinspection PhpUndefinedClassInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')->where('key', 'sorting_Albums_col')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'sorting_Albums_order')->update(['confidentiality' => '0']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', 'sorting_Albums_col')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'sorting_Albums_order')->update(['confidentiality' => '2']);
	}
};
