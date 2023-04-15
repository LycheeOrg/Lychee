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
			[
				'key' => 'unlock_password_photos_with_url_param',
				'value' => 0,
				'confidentiality' => 2,
				'cat' => 'Smart Albums',
				'type_range' => BOOL,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'unlock_password_photos_with_url_param')->delete();
	}
};
