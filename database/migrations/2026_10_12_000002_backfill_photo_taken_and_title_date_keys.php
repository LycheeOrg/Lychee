<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once 'TemporaryModels/BucketKeyBackfill.php';
require_once 'TemporaryModels/BucketKeyPhoto.php';

/**
 * Feature 091 (FR-091-16): backfills `photos.taken_key` and
 * `photos.title_date_key` for every photo, chunked. Idempotent: both keys
 * derive from `taken_at`, `taken_at_orig_tz` and `title` only.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$query = DB::table('photos');

		$progress_bar = null;
		if (!App::runningUnitTests()) {
			$progress_bar = new ProgressBar(new ConsoleOutput());
			$progress_bar->setFormat("Backfilling 'photos' keys %current%/%max% [%bar%] %percent:3s%%");
			$progress_bar->start($query->count());
		}

		$query
			->select(['id', 'title', 'taken_at', 'taken_at_orig_tz'])
			->orderBy('id')
			->chunkById(BucketKeyBackfill::CHUNK_SIZE, function ($photos) use ($progress_bar) {
				$values = $photos->map(fn ($photo) => [
					'id' => $photo->id,
					'taken_key' => BucketKeyBackfill::takenKey($photo->taken_at, $photo->taken_at_orig_tz),
					'title_date_key' => BucketKeyBackfill::titleDateKey((string) $photo->title),
				])->all();

				// https://github.com/mavinoo/laravelBatch
				batch()->update(new BucketKeyPhoto(), $values, 'id');

				$progress_bar?->advance($photos->count());
			});

		$progress_bar?->finish();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Sic! Intentionally a no-op: the columns are dropped by the schema
		// migration's own `down()`.
	}
};
