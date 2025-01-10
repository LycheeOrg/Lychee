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
		DB::table('configs')->where('key', 'editor_enabled')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'upload_processing_limit')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'public_photos_hidden')->update(['confidentiality' => '0']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'editor_enabled')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'upload_processing_limit')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'public_photos_hidden')->update(['confidentiality' => '2']);
	}
};
