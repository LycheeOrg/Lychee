<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Services\TitleSplitter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

require_once 'TemporaryModels/TitleSplitPhoto.php';

/**
 * Feature 060 (FR-060-04): backfills `title_base`/`title_index` for every
 * pre-existing `photos` row, chunked to bound memory on large installs.
 * Idempotent: recomputing from `title` (untouched by this feature) always
 * yields the same result, so a re-run is a safe no-op (S-060-11).
 */
return new class() extends Migration {
	private const CHUNK_SIZE = 1000;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('photos')
			->select(['id', 'title'])
			->orderBy('id')
			->chunkById(self::CHUNK_SIZE, function ($photos) {
				$values = $photos->map(function ($photo) {
					$title_split = TitleSplitter::split($photo->title ?? '');

					return [
						'id' => $photo->id,
						'title_base' => $title_split->base,
						'title_index' => $title_split->index,
					];
				})->all();

				$photo_instance = new TitleSplitPhoto();
				// https://github.com/mavinoo/laravelBatch
				batch()->update($photo_instance, $values, 'id');
			});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Sic! Intentionally a no-op: `title_base`/`title_index` are dropped
		// by the schema migration's own `down()`, not by this backfill.
	}
};
