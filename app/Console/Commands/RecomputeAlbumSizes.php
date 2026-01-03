<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Jobs\RecomputeAlbumSizeJob;
use App\Models\Album;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecomputeAlbumSizes extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:recompute-album-sizes
							{album_id? : Optional album ID for single-album mode}
							{--sync : Run synchronously (single-album mode only)}
							{--dry-run : Preview without making changes (bulk mode only)}
							{--chunk=1000 : Number of albums to process per batch (bulk mode only)}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Recompute size statistics. With album_id: recompute single album. Without album_id: bulk backfill all albums.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$album_id = $this->argument('album_id');

		// Dual behavior: with album_id = single-album mode, without = bulk mode
		if ($album_id !== null) {
			return $this->handleSingleAlbum($album_id);
		}

		return $this->handleBulkBackfill();
	}

	/**
	 * Handle single-album recompute mode.
	 */
	private function handleSingleAlbum(string $album_id): int
	{
		$sync = $this->option('sync');

		// Validate album exists
		$album = Album::query()->find($album_id);
		if ($album === null) {
			$this->error("Album with ID '{$album_id}' not found");

			return Command::FAILURE;
		}

		$this->info("Recomputing sizes statistics for album: {$album->title} (ID: {$album_id})");

		if ($sync) {
			// Run synchronously
			$this->info('Running synchronously...');
			try {
				RecomputeAlbumSizeJob::dispatchSync($album_id);

				$this->info('✓ Sizes statistics recomputed successfully');
				Log::info("Manual recompute completed for album {$album_id}");

				return Command::SUCCESS;
			} catch (\Exception $e) {
				$this->error('✗ Failed to recompute sizes statistics: ' . $e->getMessage());
				Log::error("Manual recompute failed for album {$album_id}: " . $e->getMessage());

				return Command::FAILURE;
			}
		}

		// Dispatch job to queue
		RecomputeAlbumSizeJob::dispatch($album_id, true);

		$this->info('✓ Job dispatched to queue');
		$this->info('  Note: Sizes statistics will be updated when the queue worker processes the job');
		Log::info("Manual recompute job dispatched for album {$album_id}");

		return Command::SUCCESS;
	}

	/**
	 * Handle bulk backfill mode for all albums.
	 */
	private function handleBulkBackfill(): int
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
			->chunk($chunk_size, function ($albums) use ($dry_run, &$processed, $bar): void {
				/** @var Album $album */
				foreach ($albums as $album) {
					if (!$dry_run) {
						// Dispatch job to recompute stats for this album
						RecomputeAlbumSizeJob::dispatch($album->id, false);
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
			$this->info("Dispatched {$processed} jobs to recompute album sizes statistics");
			$this->info('Jobs will be processed by the queue worker');
			$this->warn('Note: This operation may take some time for large galleries');
		}

		Log::info("Backfill completed: {$processed} albums processed");

		return Command::SUCCESS;
	}
}
