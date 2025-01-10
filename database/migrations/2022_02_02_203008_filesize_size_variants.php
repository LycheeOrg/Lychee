<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add a filesize column for size variants to make it
 * easier to implement filesystems with a Lychee backend
 * that needs to know in advance the size of files, for example.
 *
 * The size of the original photo is also moved to the original size variant.
 */
return new class() extends Migration {
	private const VAR_TAB = 'size_variants';
	private const PHOTO_FK = 'photo_id';
	private const TYPE_COL = 'type';
	private const ID_COL = 'id';
	private const TYPE_ORIGINAL = 0;
	private const PHOTOS_TAB = 'photos';
	private const SIZE_COL = 'filesize';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// To avoid doing I/O on every photo, which would be prohibitive
		// on large instances, and because JPEG compression makes approximations
		// from original filesize too approximative, just set filesize to 0
		// for every variant except the original, indicating that the size
		// has not been calculated yes.
		// The real calculation can be done by calling `artisan lychee:variant_filesize` from the CLI.
		// This will replace the default value by the actual filesize but takes time.
		if (!Schema::hasColumn(self::VAR_TAB, self::SIZE_COL)) {
			Schema::table(self::VAR_TAB, function (Blueprint $table) {
				$table->unsignedBigInteger(self::SIZE_COL)->nullable(false)->default(0);
			});
		}

		DB::beginTransaction();

		// Copy the filesize from photo to the original size variant
		DB::table(self::VAR_TAB)
			->where(self::VAR_TAB . '.' . self::TYPE_COL, '=', self::TYPE_ORIGINAL)
			->update([self::SIZE_COL => DB::raw('(' .
				DB::table(self::PHOTOS_TAB)
					->select([self::SIZE_COL])
					->whereColumn(self::PHOTOS_TAB . '.' . self::ID_COL, '=', self::VAR_TAB . '.' . self::PHOTO_FK)
					->toSql() .
				')'
			)]);

		/*
		 * Ideally, we would be using dropColumn. However it seems that the Eloquent implementation
		 * of dropColumn for SQLite was before SQLite supported the ALTER TABLE DROP COLUMN statement.
		 * Thus, the technique was to drop all constraints, copy to a temporary table without
		 * the deleted column, and remove the old column. This can be seen by inspecting
		 * the SQL commands when trying to run the migration (env DB_LOG_SQL=true).
		 * However, in this scenario, the command fails because of :
		 * `FOREIGN KEY constraint failed (SQL: DROP TABLE photos)`.
		 * This is a really strange bug, because redoing by hand all migration commands just work.
		 * To avoid corrupting user databases, and because SQLite now support column deletion,
		 * just run the command manually.
		 */
		// Schema::table(self::PHOTOS_TAB, function (Blueprint $table) {
		// 	$table->dropColumn(self::SIZE_COL);
		// });
		/*
		 * However, we cannot use a raw statement, because DROP COLUMN was added in SQLite 3.35,
		 * which is now widely available at the time. Thus, we change all values to 0 as a marker
		 * and will drop the column later. See PR 1239 for the entire discussion.
		 */
		// DB::statement('ALTER TABLE ' . self::PHOTOS_TAB . ' DROP COLUMN ' . self::SIZE_COL);
		DB::table(self::PHOTOS_TAB)->update([self::SIZE_COL => 0]);

		DB::commit();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::beginTransaction();

		// Copy the filesize from the original size variant (if it exists) to photos
		DB::table(self::PHOTOS_TAB)
			->addBinding(self::TYPE_ORIGINAL) // we must add the binding of the sub-query below as it is wrapped in a raw statement
			->update([self::SIZE_COL => DB::raw('COALESCE((' .
				DB::table(self::VAR_TAB)
					->select([self::SIZE_COL])
					->where(self::VAR_TAB . '.' . self::TYPE_COL, ' = ', self::TYPE_ORIGINAL)
					->whereColumn(self::VAR_TAB . '.' . self::PHOTO_FK, '=', self::PHOTOS_TAB . '.' . self::ID_COL)
					->toSql() .
				'), 0)'
			)]);

		// See comment if the upward migration.
		// Schema::table(self::VAR_TAB, function (Blueprint $table) {
		// 	$table->dropColumn(self::SIZE_COL);
		// });
		// DB::statement('ALTER TABLE ' . self::VAR_TAB . ' DROP COLUMN ' . self::SIZE_COL);
		DB::table(self::VAR_TAB)->update([self::SIZE_COL => 0]);

		DB::commit();
	}
};
