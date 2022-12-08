<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLogMaxNumLine extends Migration
{
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
				'key' => 'log_max_num_line',
				'value' => '1000',
				'confidentiality' => '2',
				'cat' => 'Admin',
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
		DB::table('configs')->where('key', '=', 'log_max_num_line')->delete();
	}
}
