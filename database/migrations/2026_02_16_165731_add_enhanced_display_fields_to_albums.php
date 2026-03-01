<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (!Schema::hasColumn('albums', 'title_color')) {
			Schema::table('albums', function (Blueprint $table) {
				$table->string('title_color', 20)->default('white')->after('album_thumb_aspect_ratio')->nullable(false);
				$table->string('title_position', 20)->default('top_left')->after('title_color')->nullable(false);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn(['title_color', 'title_position']);
		});
	}
};
