<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigLocalTakestamp extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('DISABLED') or define('DISABLED', '');

		DB::table('configs')->insert([
			[
				'key' => 'local_takestamp_video_formats',
				'value' => '.avi|.mov',
				'confidentiality' => '2',
				'cat' => 'Image Processing',
				'type_range' => DISABLED,
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
		Configs::where('key', '=', 'local_takestamp_video_formats')->delete();
	}
}
