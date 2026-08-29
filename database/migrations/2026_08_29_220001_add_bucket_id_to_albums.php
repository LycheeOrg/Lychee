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
 * Feature 061 (Album Timeline Bucket Aggregation): adds a nullable, plain
 * (non-generated) `bucket_id` column to `albums`, holding the already-truncated
 * date-bucket label (or alphabetical title prefix) for whichever date source
 * and granularity are currently in effect for that album's own parent
 * (FR-061-01, DO-061-04). Populated at write time by `RecomputeAlbumStatsJob`,
 * never computed live at read time. The composite index lets the buckets
 * endpoint's `GROUP BY bucket_id` be a plain, index-served aggregate
 * (NFR-061-01) rather than a per-row function evaluation.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->string('bucket_id')->nullable()->default(null)->after('parent_id');
			$table->index(['parent_id', 'bucket_id'], 'albums_parent_id_bucket_id_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropIndex('albums_parent_id_bucket_id_index');
			$table->dropColumn('bucket_id');
		});
	}
};
