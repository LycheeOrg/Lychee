<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class AddSidecarSetting extends Migration
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
				'key' => 'prefer_sidecar_metadata',
				'value' => 0,
				'confidentiality' => 2,
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
		Configs::where('key', '=', 'prefer_sidecar_metadata')->delete();
	}
}
