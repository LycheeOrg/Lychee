<?php

/** @noinspection PhpUndefinedClassInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		defined('BOOL') or define('BOOL', '0|1');
		defined('INT') or define('INT', 'int');

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
			'type_range' => INT,
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

		Schema::table('photos', function ($table) {
			$table->string('location')->default(null)->after('imgDirection')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'location_decoding')->delete();
		DB::table('configs')->where('key', '=', 'location_decoding_timeout')->delete();
		DB::table('configs')->where('key', '=', 'location_show')->delete();
		DB::table('configs')->where('key', '=', 'location_show_public')->delete();
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('location');
		});
	}
};
