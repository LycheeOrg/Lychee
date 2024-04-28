<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->insert([
			'key' => 'auto_fix_orientation',
			'value' => '1',
			'cat' => 'Image Processing',
			'type_range' => BOOL,
			'confidentiality' => '0',
			'description' => 'Automatically rotate imported images',
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'auto_fix_orientation')->delete();
	}
};
