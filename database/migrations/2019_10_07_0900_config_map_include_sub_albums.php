<?php

/** @noinspection PhpUndefinedClassInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigMapIncludeSubAlbums extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->insert([
			[
				'key' => 'map_include_subalbums',
				'value' => '0',
				'confidentiality' => 0,
				'cat' => 'Mod Map',
				'type_range' => BOOL,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('key', '=', 'map_include_subalbums')->delete();
	}
}
