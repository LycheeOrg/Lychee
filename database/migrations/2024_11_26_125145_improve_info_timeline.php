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
		DB::table('configs')->where('key', '=', 'timeline_photos_layout')->update(['details' => "<span class='italic'>Not available yet.</span>"]);
		DB::table('configs')->where('key', '=', 'timeline_photos_pagination_limit')->update(['details' => "<span class='italic'>Not available yet.</span>"]);
		DB::table('configs')->where('key', '=', 'timeline_photos_enabled')->update(['details' => 'Globally enable photo timelines in each albums. This can also be disabled/enabled per album.']);
		DB::table('configs')->where('key', '=', 'timeline_albums_enabled')->update(['details' => 'Globally enable albums timelines in each albums (and root). This can also be disabled/enabled per album.']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->whereIn('key', ['timeline_photos_layout', 'timeline_photos_pagination_limit', 'timeline_photos_enabled', 'timeline_albums_enabled'])->update(['details' => '']);
	}
};
