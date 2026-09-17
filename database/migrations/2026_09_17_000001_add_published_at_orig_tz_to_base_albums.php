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
 * Feature 068 (FR-068-10): adds a timezone companion column to the existing
 * `base_albums.published_at`, so it can be cast via `DateTimeWithTimezoneCast`
 * (mirrors `photos.taken_at`/`taken_at_orig_tz`'s established pattern) —
 * `published_at` itself is neither renamed nor moved (Decision Card Q-068-01).
 *
 * The backfill for existing non-null `published_at` rows is a best-effort
 * approximation: the server's *current* default timezone at migration time,
 * not necessarily whatever was actually in effect when each row was
 * originally set (no historical record of that exists) — the same accepted
 * approximation `taken_at_orig_tz`'s own historical backfill
 * (`2021_06_01_181900_refactor_timestamps_anew.php`) made.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('base_albums', function (Blueprint $table): void {
			$table->string('published_at_orig_tz', 31)->nullable(true)->default(null)->after('published_at');
		});

		DB::table('base_albums')->whereNotNull('published_at')->update([
			'published_at_orig_tz' => date_default_timezone_get(),
		]);
	}

	public function down(): void
	{
		Schema::table('base_albums', function (Blueprint $table): void {
			$table->dropColumn('published_at_orig_tz');
		});
	}
};
