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
 * Feature 092 (FR-092-16, ADR-092-01): `photo_album.bucket_id` and
 * `albums.bucket_id` become `bucket_key`, a granularity-free key whose
 * prefix is the bucket id. Each composite index is dropped before the
 * rename and recreated under the new name. `albums_parent_id_index` stays
 * as the foreign key's fallback index (see
 * `2026_09_05_120001_add_bucket_id_to_albums.php`), so dropping the
 * composite one is safe on MariaDB.
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
		$this->rename('photo_album', 'album_id', 'bucket_id', 'bucket_key');
		$this->rename('albums', 'parent_id', 'bucket_id', 'bucket_key');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->rename('photo_album', 'album_id', 'bucket_key', 'bucket_id');
		$this->rename('albums', 'parent_id', 'bucket_key', 'bucket_id');
	}

	private function rename(string $table_name, string $leading_column, string $from, string $to): void
	{
		Schema::table($table_name, function (Blueprint $table) use ($table_name, $leading_column, $from, $to) {
			$this->optimize->dropIndexIfExists($table, $table_name . '_' . $leading_column . '_' . $from . '_index');
			$this->optimize->dropIndexIfExists($table, $table_name . '_' . $leading_column . '_' . $to . '_index');
		});

		if (Schema::hasColumn($table_name, $from)) {
			Schema::table($table_name, function (Blueprint $table) use ($from, $to) {
				$table->renameColumn($from, $to);
			});
		}

		Schema::table($table_name, function (Blueprint $table) use ($table_name, $leading_column, $to) {
			$table->index([$leading_column, $to], $table_name . '_' . $leading_column . '_' . $to . '_index');
		});
	}
};
