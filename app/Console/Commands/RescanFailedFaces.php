<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Enum\FaceScanStatus;
use App\Jobs\DispatchFaceScanJob;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command for maintenance re-scanning of failed photos.
 *
 * Usage:
 *   php artisan lychee:rescan-failed-faces
 *       — re-enqueue all photos with face_scan_status = 'failed'
 *   php artisan lychee:rescan-failed-faces --stuck-pending --older-than=60
 *       — also reset photos stuck in 'pending' for > N minutes back to null,
 *         making them eligible for a fresh scan
 */
class RescanFailedFaces extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:rescan-failed-faces
							{--stuck-pending : Also reset photos stuck in pending state}
							{--older-than=60 : Minutes threshold for stuck-pending reset}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Re-enqueue failed face scan photos; optionally reset stuck pending records.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$reset_count = 0;
		$dispatched = 0;

		// Reset stuck-pending records if requested
		if ($this->option('stuck-pending') === true) {
			$older_than = (int) $this->option('older-than');
			$cutoff = Carbon::now()->subMinutes($older_than);

			$reset_count = Photo::where('face_scan_status', '=', FaceScanStatus::PENDING->value)
				->where('updated_at', '<', $cutoff)
				->update(['face_scan_status' => null]);

			$this->info("Reset {$reset_count} stuck-pending photo(s) older than {$older_than} minutes.");
			Log::info("lychee:rescan-failed-faces reset {$reset_count} stuck-pending records.");
		}

		// Re-enqueue failed photos
		Photo::query()
			->select('id')
			->where('face_scan_status', '=', FaceScanStatus::FAILED->value)
			->lazyById(200, 'id')
			->each(function (Photo $photo) use (&$dispatched): void {
				Photo::where('id', '=', $photo->id)->update(['face_scan_status' => FaceScanStatus::PENDING->value]);
				DispatchFaceScanJob::dispatch($photo->id);
				$dispatched++;
			});

		$this->info("Dispatched {$dispatched} re-scan job(s) for failed photos.");
		Log::info("lychee:rescan-failed-faces dispatched {$dispatched} re-scan jobs.");

		return Command::SUCCESS;
	}
}
