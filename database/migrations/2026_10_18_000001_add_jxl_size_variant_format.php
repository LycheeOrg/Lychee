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
	 * Allow JPEG XL as format of generated size variants.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'size_variant_format')->update([
			'type_range' => 'original|jpeg|webp|jxl',
			'details' => 'Original: thumbs are JPEG, small and medium keep the format of the uploaded file. JPEG, WebP or JPEG XL: all generated size variants use that format. Only applies to newly generated size variants. JPEG XL is not displayed by all browsers and requires Imagick with JPEG XL support (see Diagnostics).',
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// jxl is not a valid value anymore.
		DB::table('configs')->where('key', '=', 'size_variant_format')->where('value', '=', 'jxl')->update(['value' => 'original']);
		DB::table('configs')->where('key', '=', 'size_variant_format')->update([
			'type_range' => 'original|jpeg|webp',
			'details' => 'Original: thumbs are JPEG, small and medium keep the format of the uploaded file. JPEG or WebP: all generated size variants use that format. Only applies to newly generated size variants.',
		]);
	}
};
