<?php

declare(strict_types=1);

/** @noinspection PhpUndefinedClassInspection */

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
			[
				'key' => 'map_display_direction',
				'value' => '1',
				'confidentiality' => 0,
				'cat' => 'Mod Map',
				'type_range' => BOOL,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'map_display_direction')->delete();
	}
};
