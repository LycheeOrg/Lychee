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
			'key' => 'use_job_queues',
			'value' => '0',
			'cat' => 'Image Processing',
			'type_range' => BOOL,
			'confidentiality' => '0',
			'description' => 'Use job queues instead of directly live connection.',
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'use_job_queues')->delete();
	}
};
