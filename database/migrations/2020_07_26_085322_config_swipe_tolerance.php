<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
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
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'swipe_tolerance_x')->delete();
		DB::table('configs')->where('key', '=', 'swipe_tolerance_y')->delete();
	}
};
