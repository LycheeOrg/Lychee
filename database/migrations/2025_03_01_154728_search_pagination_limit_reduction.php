<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// We limit to 300 in order to reduce the load on the front-end...
		DB::table('configs')->where('key', 'search_pagination_limit')->update(['value' => '300']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'search_pagination_limit')->update(['value' => '1000']);
	}
};
