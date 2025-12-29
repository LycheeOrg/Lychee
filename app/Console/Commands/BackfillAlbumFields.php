<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillAlbumFields extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:backfill-album-fields
							{--dry-run : Preview without making changes}
							{--chunk=1000 : Number of albums to process per batch}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Backfill computed fields (min/max taken_at, num_children, num_photos, cover IDs) for all albums';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$dry_run = $this->option('dry-run');
		$chunk_size = (int) $this->option('chunk');

		if ($chunk_size < 1) {
			$this->error('Chunk size must be at least 1');

			return Command::FAILURE;
		}

		$this->info('Starting album fields backfill...');
		if ($dry_run) {
			$this->warn('DRY RUN MODE - No changes will be made');
		}

		// Get total count
		$total = Album::query()->count();
		$this->info("Found {$total} albums to process");

		if ($total === 0) {
			$this->info('No albums to process');

			return Command::SUCCESS;
		}

		// Process albums ordered by _lft ASC (leaf-to-root order)
		// This ensures child albums are computed before parents
		$bar = $this->output->createProgressBar($total);
		$bar->start();

		$processed = 0;

		Album::query()
			->orderBy('_lft', 'asc')
			->chunk($chunk_size, function (\Illuminate\Database\Eloquent\Collection $albums) use ($dry_run, &$processed, $bar) {
				/** @var Album $album */
				foreach ($albums as $album) {
					if (!$dry_run) {
						// Dispatch job to recompute stats for this album
						RecomputeAlbumStatsJob::dispatch($album->id);
					}

					$processed++;
					$bar->advance();

					// Log progress at intervals
					if ($processed % 100 === 0) {
						$percentage = round(($processed / $bar->getMaxSteps()) * 100, 2);
						Log::info("Backfilled {$processed}/{$bar->getMaxSteps()} albums ({$percentage}%)");
					}
				}
			});

		$bar->finish();
		$this->newLine(2);

		if ($dry_run) {
			$this->info("DRY RUN: Would have dispatched {$processed} jobs");
		} else {
			$this->info("Dispatched {$processed} jobs to recompute album stats");
			$this->info('Jobs will be processed by the queue worker');
			$this->warn('Note: This operation may take some time for large galleries');
		}

		Log::info("Backfill completed: {$processed} albums processed");

		return Command::SUCCESS;
	}
}
