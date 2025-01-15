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
		DB::table('configs')->where('key', 'full_photo')->update(['key' => 'grants_full_photo_access']);
		DB::table('configs')->where('key', 'downloadable')->update(['key' => 'grants_download']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'grants_full_photo_access')->update(['key' => 'full_photo']);
		DB::table('configs')->where('key', 'grants_download')->update(['key' => 'downloadable']);
	}
};
