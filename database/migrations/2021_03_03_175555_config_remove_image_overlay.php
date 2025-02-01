<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'image_overlay_type')->update(['type_range' => 'exif|desc|date|none']);
		DB::table('configs')->where('key', '=', 'image_overlay')->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'image_overlay_type')->update(['type_range' => 'exif|desc|takedate']);

		DB::table('configs')->insert([
			[
				'key' => 'image_overlay',
				'value' => '1',
				'cat' => 'Gallery',
				'type_range' => '0|1',
				'confidentiality' => '0',
			],
		]);
	}
};
