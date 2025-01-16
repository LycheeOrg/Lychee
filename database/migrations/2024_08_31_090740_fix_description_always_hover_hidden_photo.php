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
		DB::table('configs')->where('key', '=', 'display_thumb_photo_overlay')->update(['description' => 'Display the title and metadata on photo thumbs (always|hover|never)']);
		DB::table('configs')->where('key', '=', 'small_max_width')->update(['description' => 'Maximum width for small thumbs (album view)']);
		DB::table('configs')->where('key', '=', 'small_max_height')->update(['description' => 'Maximum height for small thumbs (album view)']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Nothing.
	}
};
