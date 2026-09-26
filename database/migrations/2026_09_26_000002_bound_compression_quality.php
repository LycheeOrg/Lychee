<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Bound compression_quality to 0..100 and allow 0 as "lossless".
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'compression_quality')->update([
			'type_range' => 'int:0:100',
			'description' => 'Quality of generated size variants',
			'details' => '1-100: lossy quality. 0: lossless for WebP, maximum quality (100) for formats without a lossless mode such as JPEG.',
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// 0 is not a valid positive value.
		DB::table('configs')->where('key', '=', 'compression_quality')->where('value', '=', '0')->update(['value' => '100']);
		DB::table('configs')->where('key', '=', 'compression_quality')->update([
			'type_range' => 'positive',
			'description' => 'Compression percent when generating thumbs',
			'details' => '',
		]);
	}
};
