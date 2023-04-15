<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'gen_demo_js')->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->insert([
			'key' => 'gen_demo_js',
			'value' => '0',
			'cat' => 'Admin',
			'type_range' => '0|1',
			'confidentiality' => '3',
			'description' => 'Enable generation of JS responses for demo purposes',
		]);
	}
};
