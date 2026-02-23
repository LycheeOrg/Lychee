<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

/**
 * Drop the legacy old_album_id column from the photos table.
 *
 * When the photo_album pivot was introduced, photos.album_id was renamed to
 * old_album_id for backward compatibility. This migration removes that column
 * and all composite indexes that include it, recreating the is_starred-based
 * indexes without the album_id prefix so that the subsequent rename migration
 * can continue to drop/recreate them by name.
 *
 * Note: pure sort indexes on taken_at, created_at, and type
 * (photos_album_id_taken_at_index, photos_album_id_created_at_index,
 * photos_album_id_type_index) are dropped and not recreated because standalone
 * single-column indexes on those columns already exist.
 */
return new class() extends Migration {
	private const TABLE = 'photos';
	private const OLD_COL = 'old_album_id';

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
		// ----------------------------------------------------------------
		// Step 1: Drop all indexes that include old_album_id
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_type_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_type_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_description_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_description(128)_index');
		});

		// ----------------------------------------------------------------
		// Step 2: Drop the old_album_id column
		// ----------------------------------------------------------------
		if (Schema::hasColumn(self::TABLE, self::OLD_COL)) {
			Schema::table(self::TABLE, function (Blueprint $table) {
				$table->dropColumn(self::OLD_COL);
			});
		}

		// ----------------------------------------------------------------
		// Step 3: Recreate the is_starred composite indexes without old_album_id,
		// keeping the same index names so the rename migration can drop them.
		// The pure sort indexes (taken_at, created_at, type) are not recreated
		// as equivalent single-column indexes already exist.
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_index');
			$table->index(['is_starred'], 'photos_album_id_is_starred_index');

			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_created_at_index');
			$table->index(['is_starred', 'created_at'], 'photos_album_id_is_starred_created_at_index');

			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_taken_at_index');
			$table->index(['is_starred', 'taken_at'], 'photos_album_id_is_starred_taken_at_index');

			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_type_index');
			$table->index(['is_starred', 'type'], 'photos_album_id_is_starred_type_index');

			if (DB::getDriverName() === 'mysql') {
				$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_description(128)_index');
			}
		});

		if (DB::getDriverName() === 'mysql') {
			DB::statement('ALTER TABLE `photos` ADD INDEX `photos_album_id_is_starred_description(128)_index`(`is_starred`, `description`(128))');
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// ----------------------------------------------------------------
		// Step 1: Drop the is_starred indexes created in up()
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_type_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_description(128)_index');
		});

		// ----------------------------------------------------------------
		// Step 2: Re-add old_album_id as a nullable char column
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->char(self::OLD_COL, 24)->nullable()->default(null);
		});

		// ----------------------------------------------------------------
		// Step 3: Recreate the original composite indexes with old_album_id
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->index([self::OLD_COL, 'taken_at'], 'photos_album_id_taken_at_index');
			$table->index([self::OLD_COL, 'created_at'], 'photos_album_id_created_at_index');
			$table->index([self::OLD_COL, 'is_starred'], 'photos_album_id_is_starred_index');
			$table->index([self::OLD_COL, 'type'], 'photos_album_id_type_index');
			$table->index([self::OLD_COL, 'is_starred', 'created_at'], 'photos_album_id_is_starred_created_at_index');
			$table->index([self::OLD_COL, 'is_starred', 'taken_at'], 'photos_album_id_is_starred_taken_at_index');
			$table->index([self::OLD_COL, 'is_starred', 'type'], 'photos_album_id_is_starred_type_index');
		});

		if (DB::getDriverName() === 'mysql') {
			DB::statement('ALTER TABLE `photos` ADD INDEX `photos_album_id_is_starred_description(128)_index`(`' . self::OLD_COL . '`, `is_starred`, `description`(128))');
		}
	}
};
