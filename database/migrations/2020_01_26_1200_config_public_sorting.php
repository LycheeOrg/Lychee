<?php

/** @noinspection PhpUndefinedClassInspection */

use App\Models\Configs;
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
		Configs::where('key', 'sorting_Albums_col')->update(['confidentiality' => '0']);
		Configs::where('key', 'sorting_Albums_order')->update(['confidentiality' => '0']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'sorting_Albums_col')->update(['confidentiality' => '2']);
		Configs::where('key', 'sorting_Albums_order')->update(['confidentiality' => '2']);
	}
}
