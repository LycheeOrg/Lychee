<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 071 (FR-071-02): per-album override of `album_date_scrubber_enabled`.
 * `NULL` follows the global setting.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('base_albums', function (Blueprint $table): void {
			$table->boolean('is_date_scrubber_enabled')->nullable(true)->default(null);
		});
	}

	public function down(): void
	{
		Schema::table('base_albums', function (Blueprint $table): void {
			$table->dropColumn('is_date_scrubber_enabled');
		});
	}
};
