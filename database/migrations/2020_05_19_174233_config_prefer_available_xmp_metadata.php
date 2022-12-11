<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigPreferAvailableXmpMetadata extends Migration
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
				'key' => 'prefer_available_xmp_metadata',
				'value' => '0',
				'confidentiality' => '2',
				'cat' => 'Image Processing',
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
		Configs::where('key', '=', 'prefer_available_xmp_metadata')->delete();
	}
}
