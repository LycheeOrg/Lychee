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
		$this->down(); // Make sure that |timeline is not duplicated
		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => $type_range . '|timeline']);

	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => str_replace('|timeline', '', $type_range)]);
	}
};
