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
		DB::table('configs')->insert([
			[
				'key' => 'album_subtitle_type',
				'value' => 'oldstyle',
				'confidentiality' => '0',
				'cat' => 'Gallery',
				'type_range' => 'description|takedate|creation|oldstyle',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'album_subtitle_type')->delete();
	}
};
