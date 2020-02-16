<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class LocationDecoding extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!defined('BOOL')) {
			define('BOOL', '0|1');
		}
		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				'key' => 'location_decoding',
				'value' => 'false',
				'cat' => 'Mod Map',
				'type_range' => BOOL,
				'confidentiality' => '0',
			]);
		} else {
			echo "Table configs does not exists\n";
		}
		if (Schema::hasTable('photos')) {
			Schema::table('photos', function ($table) {
				$table->string('location')->default(null)->after('imgDirection')->nullable();
			});
		} else {
			echo "Table photos does not exists\n";
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
			Configs::where('key', '=', 'location_decoding')->delete();
		}
		if (Schema::hasTable('photos')) {
			Schema::table('photos', function (Blueprint $table) {
				$table->dropColumn('location');
			});
		}

	}
}
