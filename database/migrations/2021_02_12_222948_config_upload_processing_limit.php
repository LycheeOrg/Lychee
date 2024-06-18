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
				'key' => 'upload_processing_limit',
				'value' => '4',
				'confidentiality' => 2,
				'cat' => 'Image Processing',
				'type_range' => INT,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'upload_processing_limit')->delete();
	}
};
