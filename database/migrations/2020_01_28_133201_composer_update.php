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
				'key' => 'apply_composer_update',
				'value' => '0',
				'confidentiality' => '3',
				'cat' => 'Admin',
				'type_range' => BOOL,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'apply_composer_update')->delete();
	}
};
