<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecomputeAlbumStats extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:recompute-album-stats
							{album_id : The ID of the album to recompute}
							{--sync : Run synchronously instead of dispatching a job}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Manually recompute stats for a specific album (useful for recovery after propagation failures)';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$album_id = $this->argument('album_id');
		$sync = $this->option('sync');

		// Validate album exists
		$album = Album::query()->find($album_id);
		if ($album === null) {
			$this->error("Album with ID '{$album_id}' not found");

			return Command::FAILURE;
		}

		$this->info("Recomputing stats for album: {$album->title} (ID: {$album_id})");

		if ($sync) {
			// Run synchronously
			$this->info('Running synchronously...');
			try {
				$job = new RecomputeAlbumStatsJob($album_id);
				$job->handle();

				$this->info('✓ Stats recomputed successfully');
				Log::info("Manual recompute completed for album {$album_id}");

				return Command::SUCCESS;
			} catch (\Exception $e) {
				$this->error('✗ Failed to recompute stats: ' . $e->getMessage());
				Log::error("Manual recompute failed for album {$album_id}: " . $e->getMessage());

				return Command::FAILURE;
			}
		} else {
			// Dispatch job to queue
			RecomputeAlbumStatsJob::dispatch($album_id);

			$this->info('✓ Job dispatched to queue');
			$this->info('  Note: Stats will be updated when the queue worker processes the job');
			Log::info("Manual recompute job dispatched for album {$album_id}");

			return Command::SUCCESS;
		}
	}
}
