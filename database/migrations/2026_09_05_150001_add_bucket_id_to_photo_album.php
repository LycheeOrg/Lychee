<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

/**
 * Adds a nullable, plain (non-generated) `bucket_id` column to the
 * `photo_album` pivot table — deliberately **not** on `photos` itself,
 * unlike the album-listing `albums.bucket_id` column: `photo_album` is a
 * genuine many-to-many pivot (a single `Photo` can be linked into two
 * different albums whose effective `sorting_col`/`photo_timeline` settings
 * differ, per `MoveOrDuplicate::do()`'s copy support), so a photo's bucket
 * is a property of one specific photo-in-one-specific-album edge, not of the
 * photo alone. Populated at write time by
 * {@see \App\Services\PhotoBucketComputer} via its write-site triggers,
 * never computed live at read time.
 *
 * `photo_album.album_id` already carries its own dedicated single-column
 * index (from the pivot's original `->index()` column definition) — unlike
 * `albums.parent_id` at the time of the album-listing bucket_id migration,
 * so the foreign-key-rebind hazard documented in
 * `2026_09_05_120001_add_bucket_id_to_albums.php` does not apply here: the
 * new composite `(album_id, bucket_id)` index below is purely additive and
 * always safe to drop in `down()`.
 */
return new class() extends Migration {
	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// Ensuring idempotency.
		Schema::table('photo_album', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photo_album_album_id_bucket_id_index');
		});

		Schema::table('photo_album', function (Blueprint $table) {
			$table->string('bucket_id')->nullable()->default(null)->after('album_id');
			$table->index(['album_id', 'bucket_id'], 'photo_album_album_id_bucket_id_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photo_album', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photo_album_album_id_bucket_id_index');
			$table->dropColumn('bucket_id');
		});
	}
};
