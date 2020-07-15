<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigLosslessOptimization extends Migration
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
				'key' => 'lossless_optimization',
				'value' => '1',
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
		Configs::where('key', '=', 'lossless_optimization')->delete();
	}
}
