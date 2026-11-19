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
 * Feature 067 (Map Geo-Bucketing): adds a plain composite index on
 * `photos(latitude, longitude)` — today there is none at all. A
 * bounding-box `WHERE` filter + grid `GROUP BY` at scale would otherwise be
 * a full table scan regardless of how well the query itself is written
 * (FR-067-14, NFR-067-01).
 *
 * A plain B-tree index, not a driver-specific spatial index type
 * (MySQL `SPATIAL INDEX`, PostgreSQL `GiST`/PostGIS) — this project
 * supports sqlite/mysql/mariadb/pgsql uniformly with a single migration
 * path, and sqlite has no built-in spatial index without the SpatiaLite
 * extension, which cannot be assumed present (Q-067-03).
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
		Schema::table('photos', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_latitude_longitude_index');
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->index(['latitude', 'longitude'], 'photos_latitude_longitude_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_latitude_longitude_index');
		});
	}
};
