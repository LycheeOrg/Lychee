<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigMapProviders extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!defined('MAP_PROVIDERS')) {
			define('MAP_PROVIDERS', 'Wikimedia|OpenStreetMap.org|OpenStreetMap.de|OpenStreetMap.fr|RRZE');
		}

		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'map_provider',
					'value' => 'Wikimedia',
					'confidentiality' => 0,
					'cat' => 'Mod Map',
					'type_range' => MAP_PROVIDERS,
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
			Configs::where('key', '=', 'map_provider')->delete();
		}
	}
}
