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
		defined('BOOL') or define('BOOL', '0|1');
		DB::table('configs')->insert([
			[
				'key' => 'editor_enabled',
				'value' => '1',
				'confidentiality' => '2',
				'cat' => 'Image Processing',
				'type_range' => BOOL,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'editor_enabled')->delete();
	}
};
