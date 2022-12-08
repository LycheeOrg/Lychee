<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixSortingConfig extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')->where('key', 'sorting_Albums_col')->update(['key' => 'sorting_albums_col']);
		DB::table('configs')->where('key', 'sorting_Albums_order')->update(['key' => 'sorting_albums_order']);
		DB::table('configs')->where('key', 'sorting_Photos_col')->update(['key' => 'sorting_photos_col']);
		DB::table('configs')->where('key', 'sorting_Photos_order')->update(['key' => 'sorting_photos_order']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', 'sorting_albums_col')->update(['key' => 'sorting_Albums_col']);
		DB::table('configs')->where('key', 'sorting_albums_order')->update(['key' => 'sorting_Albums_order']);
		DB::table('configs')->where('key', 'sorting_photos_col')->update(['key' => 'sorting_Photos_col']);
		DB::table('configs')->where('key', 'sorting_photos_order')->update(['key' => 'sorting_Photos_order']);
	}
}
