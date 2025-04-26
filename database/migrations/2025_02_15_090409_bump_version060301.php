<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'cache_ttl')->update(['value' => '86400']);
		DB::table('configs')->where('key', 'version')->update(['value' => '060301']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'cache_ttl')->update(['value' => '300']);
		DB::table('configs')->where('key', 'version')->update(['value' => '060300']);
	}
};
