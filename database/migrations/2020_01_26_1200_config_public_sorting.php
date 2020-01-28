<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigPublicSorting extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Configs::where('key', 'sorting_Albums_col')->exists()) {
			Configs::where('key', 'sorting_Albums_col')->update(['confidentiality' => '0']);
		}
		if (Configs::where('key', 'sorting_Albums_order')->exists()) {
			Configs::where('key', 'sorting_Albums_order')->update(['confidentiality' => '0']);
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
			if (Configs::where('key', 'sorting_Albums_col')->exists()) {
				Configs::where('key', 'sorting_Albums_col')->update(['confidentiality' => '2']);
			}
			if (Configs::where('key', 'sorting_Albums_order')->exists()) {
				Configs::where('key', 'sorting_Albums_order')->update(['confidentiality' => '2']);
			}
		}
	}
}
