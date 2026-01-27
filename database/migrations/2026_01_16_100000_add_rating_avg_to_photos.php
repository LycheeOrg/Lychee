<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/RatedPhoto.php';

return new class() extends Migration {
	private const TABLE = 'photos';
	private const COL_RATING_AVG = 'rating_avg';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (!Schema::hasColumn(self::TABLE, self::COL_RATING_AVG)) {
			Schema::table(self::TABLE, function (Blueprint $table) {
				// Add rating_avg column with 4 decimal places for fine granularity (Q-009-05)
				// DECIMAL(5,4) allows values 0.0000-9.9999, sufficient for 1.0-5.0 rating averages
				$table->decimal(self::COL_RATING_AVG, 5, 4)->nullable()->after('is_starred');

				// Add index for efficient sorting by rating (FR-009-02, NFR-009-01)
				$table->index(self::COL_RATING_AVG);
			});
		}

		DB::transaction(function () {
			// Count photos that need updating
			$photosToUpdate = DB::table('photos')
				->join('statistics', 'photos.id', '=', 'statistics.photo_id')
				->where('statistics.rating_count', '>', 0)
				->count();

			if ($photosToUpdate === 0) {
				return;
			}

			// Process in chunks to avoid memory issues
			DB::table('photos')
				->join('statistics', 'photos.id', '=', 'statistics.photo_id')
				->where('statistics.rating_count', '>', 0)
				->select('photos.id', 'statistics.rating_sum', 'statistics.rating_count')
				->orderBy('photos.id')
				->chunkById(100, function ($photos) {
					$update = $photos->map(function ($photo) {
						$ratingAvg = round($photo->rating_sum / $photo->rating_count, 4);

						return [
							'id' => $photo->id,
							'rating_avg' => $ratingAvg,
						];
					})->all();

					$key_name = 'id';
					$photo_instance = new RatedPhoto();
					batch()->update($photo_instance, $update, $key_name);
				}, 'photos.id', 'id');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->dropIndex([self::COL_RATING_AVG]);
			$table->dropColumn(self::COL_RATING_AVG);
		});
	}
};
