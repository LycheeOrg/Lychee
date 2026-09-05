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
require_once 'TemporaryModels/TitleSplitBaseAlbum.php';

/**
 * Feature 060 (FR-060-04): backfills `title_base`/`title_index` for every
 * pre-existing `base_albums` row, chunked to bound memory on large installs.
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
		$query = DB::table('base_albums');

		$progress_bar = null;
		if (!App::runningUnitTests()) {
			$progress_bar = new ProgressBar(new ConsoleOutput());
			$progress_bar->setFormat("Backfilling 'base_albums' %current%/%max% [%bar%] %percent:3s%%");
			$progress_bar->start($query->count());
		}

		$query
			->select(['id', 'title'])
			->orderBy('id')
			->chunkById(self::CHUNK_SIZE, function ($albums) use ($progress_bar) {
				$values = $albums->map(function ($album) {
					$title_split = TitleSplitter::split($album->title);

					return [
						'id' => $album->id,
						'title_base' => $title_split->base,
						'title_index' => $title_split->index,
					];
				})->all();

				$album_instance = new TitleSplitBaseAlbum();
				// https://github.com/mavinoo/laravelBatch
				batch()->update($album_instance, $values, 'id');

				$progress_bar?->advance($albums->count());
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
