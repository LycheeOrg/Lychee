<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
				'value' => '0',
				'cat' => 'Mod Map',
				'type_range' => BOOL,
				'confidentiality' => '0',
			]);
			DB::table('configs')->insert([
				'key' => 'location_decoding_timeout',
				'value' => 30,
				'cat' => 'Mod Map',
				'type_range' => 'int',
				'confidentiality' => '0',
			]);
			DB::table('configs')->insert([
				'key' => 'location_decoding_caching_type',
				'value' => 'Harddisk',
				'cat' => 'Mod Map',
				'type_range' => 'None|Memory|Harddisk',
				'confidentiality' => '0',
			]);
			DB::table('configs')->insert([
				'key' => 'location_show',
				'value' => '1',
				'cat' => 'Mod Map',
				'type_range' => BOOL,
				'confidentiality' => '0',
			]);
			DB::table('configs')->insert([
				'key' => 'location_show_public',
				'value' => '0',
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
			Configs::where('key', '=', 'location_decoding_timeout')->delete();
			Configs::where('key', '=', 'location_decoding_caching_type')->delete();
			Configs::where('key', '=', 'location_show')->delete();
			Configs::where('key', '=', 'location_show_public')->delete();
		}
		if (Schema::hasTable('photos')) {
			Schema::table('photos', function (Blueprint $table) {
				$table->dropColumn('location');
			});
		}
	}
}
