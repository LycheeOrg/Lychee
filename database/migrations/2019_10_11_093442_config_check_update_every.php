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
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'update_check_every_days')->delete();
	}
};
