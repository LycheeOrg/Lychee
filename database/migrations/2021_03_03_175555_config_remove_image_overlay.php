<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'image_overlay_type')->update(['type_range' => 'exif|desc|date|none']);
		DB::table('configs')->where('key', '=', 'image_overlay')->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->where('key', '=', 'image_overlay_type')->update(['type_range' => 'exif|desc|takedate']);

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
