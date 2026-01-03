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

/**
 * Manually recompute size statistics for a specific album (T-004-39, T-004-40, CLI-004-02).
 *
 * This command is useful for manual recovery when statistics drift out of sync.
 */
class RecomputeAlbumSizes extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:recompute-album-sizes {album_id : The ID of the album to recompute}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Manually recompute size statistics for a specific album';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$album_id = $this->argument('album_id');

		// Validate album exists
		$album = Album::find($album_id);
		if ($album === null) {
			$this->error("Album with ID '{$album_id}' not found");

			return Command::FAILURE;
		}

		$this->info("Recomputing size statistics for album: {$album->title} (ID: {$album_id})");

		// Dispatch job
		RecomputeAlbumSizeJob::dispatch($album_id);

		$this->info('Job dispatched successfully');
		$this->info('Statistics will be updated by the queue worker');

		Log::info("Manual recompute triggered for album {$album_id} via CLI");

		return Command::SUCCESS;
	}
}
