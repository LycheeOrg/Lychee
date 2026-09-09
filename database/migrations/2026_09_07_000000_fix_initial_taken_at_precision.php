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
 * `photos.taken_at` is `datetime(6)` (microsecond precision), but
 * `photos.initial_taken_at` — its own "backup of the original taken_at
 * value" (per its own column comment) — was created with precision `0`
 * (`2025_01_24_200235_add_initial_taken_at.php`). Since both columns are set
 * from the same `Carbon` instant at write time (upload, and any edit that
 * assigns `taken_at`), the mismatched precision means `initial_taken_at`
 * silently truncates away the sub-second part of whatever it's backing up —
 * so it is *not* actually a lossless backup, and
 * `PhotoController::update()`'s `$photo->taken_at = $request->takenAt() ??
 * $photo->initial_taken_at;` fallback (used whenever the request doesn't
 * explicitly change `taken_at`) reintroduces a genuine, if tiny, value
 * change every time it fires — which
 * `RecomputePhotoBucketsJob::dispatchIf($photo->wasChanged([..., 'taken_at']), ...)`
 * then correctly (if unhelpfully) detects, spuriously recomputing bucket
 * placement for what should have been a no-op. This only surfaces on
 * database engines that actually enforce column precision at write time
 * (MariaDB/MySQL, PostgreSQL) — SQLite stores whatever string is given
 * regardless of declared precision, so the same photo's `taken_at`/
 * `initial_taken_at` never actually diverge there, which is why this went
 * unnoticed until CI's non-SQLite matrix legs caught it
 * ({@see \Tests\Feature_v2\Photo\PhotoSortingBucketDispatchTest::testUpdateUnrelatedAttributeChangeDoesNotDispatch}).
 *
 * This migration is schema-only: existing rows whose `initial_taken_at` has
 * already lost its sub-second precision stay as-is (there is nothing to
 * recover it from) — only future writes benefit from the fix.
 */
return new class() extends Migration {
	private const TABLE = 'photos';
	private const COLUMN = 'initial_taken_at';
	private const NEW_PRECISION = 6;
	private const OLD_PRECISION = 0;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$table->dateTime(self::COLUMN, self::NEW_PRECISION)
				->nullable(true)
				->default(null)
				->comment('backup of the original taken_at value')
				->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table): void {
			$table->dateTime(self::COLUMN, self::OLD_PRECISION)
				->nullable(true)
				->default(null)
				->comment('backup of the original taken_at value')
				->change();
		});
	}
};
