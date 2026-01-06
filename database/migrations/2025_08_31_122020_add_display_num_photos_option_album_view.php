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
		$this->down(); // Make sure that |timeline is not duplicated
		$type_range = DB::table('configs')->select('type_range')->where('key', 'album_subtitle_type')->first()->type_range;
		DB::table('configs')->where('key', 'album_subtitle_type')->update(['type_range' => $type_range . '|num_photos|num_albums|num_photos_albums']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$type_range = DB::table('configs')->select('type_range')->where('key', 'album_subtitle_type')->first()->type_range;
		DB::table('configs')->where('key', 'album_subtitle_type')->update(['type_range' => str_replace('|num_photos|num_albums|num_photos_albums', '', $type_range)]);
	}
};
