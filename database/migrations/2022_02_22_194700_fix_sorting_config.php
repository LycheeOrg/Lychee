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
		DB::table('configs')->where('key', 'sorting_Albums_col')->update(['key' => 'sorting_albums_col']);
		DB::table('configs')->where('key', 'sorting_Albums_order')->update(['key' => 'sorting_albums_order']);
		DB::table('configs')->where('key', 'sorting_Photos_col')->update(['key' => 'sorting_photos_col']);
		DB::table('configs')->where('key', 'sorting_Photos_order')->update(['key' => 'sorting_photos_order']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'sorting_albums_col')->update(['key' => 'sorting_Albums_col']);
		DB::table('configs')->where('key', 'sorting_albums_order')->update(['key' => 'sorting_Albums_order']);
		DB::table('configs')->where('key', 'sorting_photos_col')->update(['key' => 'sorting_Photos_col']);
		DB::table('configs')->where('key', 'sorting_photos_order')->update(['key' => 'sorting_Photos_order']);
	}
};
