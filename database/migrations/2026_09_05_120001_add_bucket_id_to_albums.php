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
 * Adds a nullable, plain (non-generated) `bucket_id` column to `albums`,
 * holding the already-truncated date-bucket label (or alphabetical title prefix)
 * for whichever date source and granularity are currently in effect for that
 * album's own parent. Populated at write time by `RecomputeAlbumStatsJob`,
 * never computed live at read time. The composite index lets the buckets
 * endpoint's `GROUP BY bucket_id` be a plain, index-served aggregate
 * rather than a per-row function evaluation.
 *
 * `albums.parent_id` (`albums_parent_id_foreign`) has never had a dedicated
 * single-column index of its own — only ever whichever index happened to have
 * `parent_id` as a leftmost column. Adding the composite `(parent_id,
 * bucket_id)` index below gives InnoDB/MariaDB a second such candidate, and it
 * can rebind the foreign key to depend on it instead — which then makes
 * `down()`'s `dropIndex()` fail with "Cannot drop index ...: needed in a
 * foreign key constraint" (confirmed via CI on mariadb). Fixed by also adding
 * a plain, permanent `albums_parent_id_index`, kept even after `down()`
 * reverts this migration, so the foreign key always has a fallback index and
 * the composite one is always safe to drop.
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
		// Ensuring idem potency.
		Schema::table('albums', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'albums_parent_id_index');
			$this->optimize->dropIndexIfExists($table, 'albums_parent_id_bucket_id_index');
		});

		Schema::table('albums', function (Blueprint $table) {
			$table->string('bucket_id')->nullable()->default(null)->after('parent_id');
			$table->index('parent_id', 'albums_parent_id_index');
			$table->index(['parent_id', 'bucket_id'], 'albums_parent_id_bucket_id_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			// albums_parent_id_index is intentionally NOT dropped here - it's
			// the foreign key's permanent fallback index, see the class docblock.
			$this->optimize->dropIndexIfExists($table, 'albums_parent_id_bucket_id_index');
			$table->dropColumn('bucket_id');
		});
	}
};
