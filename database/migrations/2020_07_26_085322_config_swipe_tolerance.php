<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('INT') or define('INT', 'int');
		DB::table('configs')->insert([
			[
				'key' => 'swipe_tolerance_x',
				'value' => '150',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => INT,
			],
			[
				'key' => 'swipe_tolerance_y',
				'value' => '250',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => INT,
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
		DB::table('configs')->where('key', '=', 'swipe_tolerance_x')->delete();
		DB::table('configs')->where('key', '=', 'swipe_tolerance_y')->delete();
	}
};
