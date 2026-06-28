<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->down();
		$type_range = DB::table('configs')->select('type_range')->where('key', 'album_subtitle_type')->first()->type_range;
		DB::table('configs')->where('key', 'album_subtitle_type')->update(['type_range' => 'disabled|' . $type_range]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$type_range = DB::table('configs')->select('type_range')->where('key', 'album_subtitle_type')->first()->type_range;
		DB::table('configs')->where('key', 'album_subtitle_type')->update(['type_range' => str_replace('disabled|', '', $type_range)]);
	}
};
