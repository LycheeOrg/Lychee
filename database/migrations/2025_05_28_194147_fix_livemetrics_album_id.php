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
		DB::statement('UPDATE live_metrics SET album_id = (SELECT album_id FROM photos WHERE photos.id = live_metrics.photo_id) WHERE photo_id IS NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Reset the albums to null for the photos that have an associated album.
		DB::table('live_metrics')->whereNotNull('photo_id')->update(['album_id' => null]);
	}
};
