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
		defined('DISABLED') or define('DISABLED', '');

		DB::table('configs')->insert([
			[
				'key' => 'local_takestamp_video_formats',
				'value' => '.avi|.mov',
				'confidentiality' => '2',
				'cat' => 'Image Processing',
				'type_range' => DISABLED,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'local_takestamp_video_formats')->delete();
	}
};
