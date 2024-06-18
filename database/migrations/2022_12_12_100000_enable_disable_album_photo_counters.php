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
				'key' => 'album_decoration',
				'value' => 'layers',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => 'none|layers|album|photo|all',
				'description' => 'Show decorations on album cover (sub-album and/or photo count)',
			],
			[
				'key' => 'album_decoration_orientation',
				'value' => 'row',
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => 'column|column-reverse|row|row-reverse',
				'description' => 'Align album decorations horizontally or vertically',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->whereIn('key', ['album_decoration', 'album_decoration_orientation'])->delete();
	}
};
