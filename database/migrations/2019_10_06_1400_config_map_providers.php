<?php

/** @noinspection PhpUndefinedClassInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigMapProviders extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('MAP_PROVIDERS') or define('MAP_PROVIDERS', 'Wikimedia|OpenStreetMap.org|OpenStreetMap.de|OpenStreetMap.fr|RRZE');

		DB::table('configs')->insert([
			[
				'key' => 'map_provider',
				'value' => 'Wikimedia',
				'confidentiality' => 0,
				'cat' => 'Mod Map',
				'type_range' => MAP_PROVIDERS,
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
		DB::table('configs')->where('key', '=', 'map_provider')->delete();
	}
}
