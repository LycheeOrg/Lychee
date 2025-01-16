<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

/**
 * Removes some troublesome indices from DB.
 *
 * These troublesome indices mislead the MySQL query planner to pick the wrong
 * indices during query optimization.
 * Note, that we only drop single-column indices, but keep the multi-column
 * indices.
 * For example, we drop the index on `photos.taken_at` but keep the index
 * on `(photos.album_id, photos.taken_at)`.
 * Laravel never sorts _all_ photos of a gallery acc. to `taken_at`, it
 * only sorts photos of a specific album.
 * Hence, the multi-column indices are crucial and absolutely necessary
 * for efficient queries, but the single-column indices only disorient the
 * query planner.
 */
return new class() extends Migration {
	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_updated_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_is_public_index');
			$this->optimize->dropIndexIfExists($table, 'photos_is_starred_index');
		});

		Schema::table('sym_links', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'sym_links_updated_at_index');
		});
	}

	public function down(): void
	{
	}
};