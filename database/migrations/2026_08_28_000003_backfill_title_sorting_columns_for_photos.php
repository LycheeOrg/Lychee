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

require_once 'TemporaryModels/TitleSplitter.php';
require_once 'TemporaryModels/TitleSplitPhoto.php';

/**
 * Feature 060 (FR-060-04): backfills `title_base`/`title_index` for every
 * pre-existing `photos` row, chunked to bound memory on large installs.
 * Idempotent: recomputing from `title` (untouched by this feature) always
 * yields the same result, so a re-run is a safe no-op (S-060-11).
 * Resumable: only rows with `title_base IS NULL` (never written by the app,
 * which always computes it - see the schema migration) are selected, so a
 * crash partway through does not force reprocessing already-backfilled rows.
 */
return new class() extends Migration {
	private const CHUNK_SIZE = 1000;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$query = DB::table('photos')->whereNull('title_base');

		$progress_bar = null;
		if (!App::runningUnitTests()) {
			$progress_bar = new ProgressBar(new ConsoleOutput());
			$progress_bar->setFormat("Backfilling 'photos' %current%/%max% [%bar%] %percent:3s%%");
			$progress_bar->start($query->count());
		}

		$query
			->select(['id', 'title'])
			->orderBy('id')
			->chunkById(self::CHUNK_SIZE, function ($photos) use ($progress_bar) {
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

				$progress_bar?->advance($photos->count());
			});

		$progress_bar?->finish();
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
