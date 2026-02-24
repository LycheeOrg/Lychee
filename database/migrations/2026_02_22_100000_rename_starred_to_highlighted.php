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
 * Rename is_starred to is_highlighted.
 *
 * - For each photo with is_starred=true AND its owner has no existing rating,
 *   insert a rating of 5 into photo_ratings and update the statistics aggregate.
 * - Recompute photos.rating_avg for all affected photos.
 * - Drop all indexes that include is_starred, rename the column to is_highlighted,
 *   and recreate equivalent indexes under the new name.
 * - Rename the enable_starred config key to enable_highlighted.
 * - Update the sorting_photos_col type_range.
 */
return new class() extends Migration {
	private const TABLE = 'photos';
	private const OLD_COL = 'is_starred';
	private const NEW_COL = 'is_highlighted';

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
		// Step 1: Data migration – convert starred photos to 5-star ratings
		// Skipped if is_starred was already renamed to is_highlighted.
		// ----------------------------------------------------------------
		if (Schema::hasColumn(self::TABLE, self::OLD_COL)) {
			DB::transaction(function () {
				DB::table('photos')
					->where(self::OLD_COL, '=', true)
					->whereNotNull('owner_id')
					->select(['id', 'owner_id'])
					->orderBy('id')
					->chunk(500, function ($photos) {
						$insert = [];
						foreach ($photos as $photo) {
							$exists = DB::table('photo_ratings')
								->where('photo_id', '=', $photo->id)
								->where('user_id', '=', $photo->owner_id)
								->exists();

							if ($exists) {
								continue;
							}

							$insert[] = [
								'photo_id' => $photo->id,
								'user_id' => $photo->owner_id,
								'rating' => 5,
							];
						}

						// Insert the 5-star rating
						DB::table('photo_ratings')->insert($insert);

						// Update statistics aggregate
						$photo_ids = array_column($insert, 'photo_id');
						DB::table('statistics')
							->whereIn('photo_id', $photo_ids)
							->increment('rating_sum', 5);

						DB::table('statistics')
							->whereIn('photo_id', $photo_ids)
							->increment('rating_count', 1);
					});
			});
		}

		// ----------------------------------------------------------------
		// Step 2: Recompute rating_avg for all photos that are now rated
		// ----------------------------------------------------------------
		DB::table('photos')
			->join('statistics', 'photos.id', '=', 'statistics.photo_id')
			->where('statistics.rating_count', '>', 0)
			->select('photos.id', 'statistics.rating_sum', 'statistics.rating_count')
			->orderBy('photos.id')
			->chunk(500, function ($rows) {
				foreach ($rows as $row) {
					DB::table('photos')
						->where('id', '=', $row->id)
						->update(['rating_avg' => round($row->rating_sum / $row->rating_count, 4)]);
				}
			});

		// ----------------------------------------------------------------
		// Step 3: Drop indexes containing is_starred, rename column, re-add
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_type_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_description(128)_index');
			$this->optimize->dropIndexIfExists($table, 'photos_is_starred_title_taken_created_index');
		});

		if (Schema::hasColumn(self::TABLE, self::OLD_COL)) {
			Schema::table(self::TABLE, function (Blueprint $table) {
				$table->renameColumn(self::OLD_COL, self::NEW_COL);
			});
		}

		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_index');
			$table->index([self::NEW_COL], 'photos_album_id_is_highlighted_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_created_at_index');
			$table->index([self::NEW_COL, 'created_at'], 'photos_album_id_is_highlighted_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_taken_at_index');
			$table->index([self::NEW_COL, 'taken_at'], 'photos_album_id_is_highlighted_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_type_index');
			$table->index([self::NEW_COL, 'type'], 'photos_album_id_is_highlighted_type_index');
			$this->optimize->dropIndexIfExists($table, 'photos_is_highlighted_title_taken_created_index');
			$table->index([self::NEW_COL, 'title', 'taken_at', 'created_at'], 'photos_is_highlighted_title_taken_created_index');
		});

		// ----------------------------------------------------------------
		// Step 4: Rename enable_starred config key → enable_highlighted
		// ----------------------------------------------------------------
		DB::table('configs')
			->where('key', '=', 'enable_starred')
			->update(['key' => 'enable_highlighted']);

		// ----------------------------------------------------------------
		// Step 5: Update sorting type_range to use is_highlighted
		// ----------------------------------------------------------------
		DB::table('configs')
			->where('key', '=', 'sorting_photos_col')
			->update([
				'type_range' => DB::raw("REPLACE(type_range, 'is_starred', 'is_highlighted')"),
			]);

		DB::table('configs')
			->where('key', '=', 'sorting_photos_col')
			->where('value', '=', 'is_starred')
			->update([
				'value' => 'is_highlighted',
			]);

		// ----------------------------------------------------------------
		// Step 6: Update random_album_id to use highlighted
		// ----------------------------------------------------------------
		DB::table('configs')
			->where('key', '=', 'random_album_id')
			->where('value', '=', 'starred')
			->update([
				'value' => 'highlighted',
			]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * Note: photo_ratings rows inserted during up() are NOT removed because
	 * we cannot reliably distinguish them from pre-existing user ratings.
	 */
	public function down(): void
	{
		// ----------------------------------------------------------------
		// Step 1: Drop is_highlighted indexes
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_created_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_taken_at_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_type_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_highlighted_description_128_index');
			$this->optimize->dropIndexIfExists($table, 'photos_is_highlighted_title_taken_created_index');
		});

		// ----------------------------------------------------------------
		// Step 2: Rename column back
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->renameColumn(self::NEW_COL, self::OLD_COL);
		});

		// ----------------------------------------------------------------
		// Step 3: Recreate original is_starred indexes
		// ----------------------------------------------------------------
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->index([self::OLD_COL], 'photos_album_id_is_starred_index');
			$table->index([self::OLD_COL, 'created_at'], 'photos_album_id_is_starred_created_at_index');
			$table->index([self::OLD_COL, 'taken_at'], 'photos_album_id_is_starred_taken_at_index');
			$table->index([self::OLD_COL, 'type'], 'photos_album_id_is_starred_type_index');
			$table->index([self::OLD_COL, 'title', 'taken_at', 'created_at'], 'photos_is_starred_title_taken_created_index');
		});

		if (DB::getDriverName() === 'mysql') {
			DB::statement('ALTER TABLE `photos` ADD INDEX `photos_album_id_is_starred_description(128)_index`(`is_starred`, `description`(128))');
		}

		// ----------------------------------------------------------------
		// Step 4: Restore enable_highlighted config key → enable_starred
		// ----------------------------------------------------------------
		DB::table('configs')
			->where('key', '=', 'enable_highlighted')
			->update(['key' => 'enable_starred']);

		// ----------------------------------------------------------------
		// Step 5: Restore sorting type_range
		// ----------------------------------------------------------------
		DB::table('configs')
			->where('key', '=', 'sorting_photos_col')
			->update([
				'type_range' => DB::raw("REPLACE(type_range, 'is_highlighted', 'is_starred')"),
			]);
		DB::table('configs')
			->where('key', '=', 'sorting_photos_col')
			->where('value', '=', 'is_highlighted')
			->update([
				'value' => 'is_starred',
			]);

		// ----------------------------------------------------------------
		// Step 6: Update random_album_id to use starred
		// ----------------------------------------------------------------
		DB::table('configs')
			->where('key', '=', 'random_album_id')
			->where('value', '=', 'highlighted')
			->update([
				'value' => 'starred',
			]);
	}
};
