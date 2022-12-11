<?php

use App\Models\Configs;
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
		Configs::where('key', '=', 'image_overlay_type')->update(['type_range' => 'exif|desc|date|none']);
		Configs::where('key', '=', 'image_overlay')->delete();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		defined('BOOL') or define('BOOL', '0|1');

		Configs::where('key', '=', 'image_overlay_type')->update(['type_range' => 'exif|desc|takedate']);

		DB::table('configs')->insert([
			[
				'key' => 'image_overlay',
				'value' => '1',
				'cat' => 'Gallery',
				'type_range' => BOOL,
				'confidentiality' => '0',
			],
		]);
	}
};
