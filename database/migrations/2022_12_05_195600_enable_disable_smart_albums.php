<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->insert([
			[
				'key' => 'SA_enabled',
				'value' => '1',
				'confidentiality' => 2,
				'cat' => 'Smart Albums',
				'type_range' => BOOL,
				'description' => 'Enable Smart Albums',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'SA_enabled')->delete();
	}
};
