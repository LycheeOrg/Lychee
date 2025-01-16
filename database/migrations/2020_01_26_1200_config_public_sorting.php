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
		DB::table('configs')->where('key', 'sorting_Albums_col')->update(['confidentiality' => '0']);
		DB::table('configs')->where('key', 'sorting_Albums_order')->update(['confidentiality' => '0']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'sorting_Albums_col')->update(['confidentiality' => '2']);
		DB::table('configs')->where('key', 'sorting_Albums_order')->update(['confidentiality' => '2']);
	}
};
