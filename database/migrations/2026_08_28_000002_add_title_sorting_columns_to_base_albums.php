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
 * to `base_albums`, computed at write time by `App\Services\TitleSplitter`
 * (FR-060-01, DO-060-03/04).
 * `title_base` mirrors `base_albums.title`'s NOT NULL constraint; it is
 * added with a temporary `''` default (safe for existing rows) which the
 * backfill migration immediately overwrites with the real computed value.
 * These are plain, non-generated columns (NFR-060-01).
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('base_albums', function (Blueprint $table) {
			$table->string('title_base', 100)->nullable(false)->default('')->after('title');
			$table->unsignedBigInteger('title_index')->nullable(false)->default(0)->after('title_base');
			$table->index(['title_base', 'title_index'], 'base_albums_title_base_title_index_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('base_albums', function (Blueprint $table) {
			$table->dropIndex('base_albums_title_base_title_index_index');
			$table->dropColumn(['title_base', 'title_index']);
		});
	}
};
