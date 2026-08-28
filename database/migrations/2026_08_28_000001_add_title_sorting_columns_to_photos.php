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
 * Feature 060 (Database-Driven Title Sorting): adds `title_base`/`title_index`
 * to `photos`, computed at write time by `App\Services\TitleSplitter`
 * (FR-060-01, DO-060-01/02).
 * These are plain, non-generated columns (NFR-060-01) - see the backfill
 * migration for pre-existing rows.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->string('title_base', 300)->nullable()->after('title');
			$table->unsignedBigInteger('title_index')->nullable()->after('title_base');
			$table->index(['title_base', 'title_index'], 'photos_title_base_title_index_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->dropIndex('photos_title_base_title_index_index');
			$table->dropColumn(['title_base', 'title_index']);
		});
	}
};
