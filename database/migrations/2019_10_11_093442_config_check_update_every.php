<?php

/** @noinspection PhpUndefinedClassInspection */

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
				'key' => 'update_check_every_days',
				'value' => '3',
				'confidentiality' => 2,
				'cat' => 'Config',
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
		DB::table('configs')->where('key', '=', 'update_check_every_days')->delete();
	}
};
