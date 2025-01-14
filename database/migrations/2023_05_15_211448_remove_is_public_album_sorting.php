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
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->where('value', '=', 'is_public')->update(['value' => 'max_taken_at']);
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['type_range' => 'created_at|title|description|max_taken_at|min_taken_at']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'sorting_albums_col')->update(['type_range' => 'created_at|title|description|is_public|max_taken_at|min_taken_at']);
	}
};
