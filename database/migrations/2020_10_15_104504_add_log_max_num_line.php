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
				'key' => 'log_max_num_line',
				'value' => '1000',
				'confidentiality' => '2',
				'cat' => 'Admin',
				'type_range' => INT,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'log_max_num_line')->delete();
	}
};
