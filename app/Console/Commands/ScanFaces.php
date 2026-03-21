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
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to enqueue photos for face detection.
 *
 * Usage:
 *   php artisan lychee:scan-faces              — all unscanned photos
 *   php artisan lychee:scan-faces --album={id} — only direct photos in given album
 */
class ScanFaces extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:scan-faces
							{--album= : Album ID — only direct photos in this album (non-recursive)}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Enqueue unscanned photos for AI Vision face detection.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$album_id = $this->option('album');

		$query = Photo::query()->select('id')->whereNull('face_scan_status');

		if ($album_id !== null) {
			$query->whereHas('albums', fn ($q) => $q->where('albums.id', '=', $album_id));
		}

		$dispatched = 0;

		$query->lazyById(200, 'id')->each(function (Photo $photo) use (&$dispatched): void {
			Photo::where('id', '=', $photo->id)->update(['face_scan_status' => FaceScanStatus::PENDING->value]);
			DispatchFaceScanJob::dispatch($photo->id);
			$dispatched++;
		});

		$this->info("Dispatched {$dispatched} face scan job(s).");
		Log::info("lychee:scan-faces dispatched {$dispatched} jobs.");

		return Command::SUCCESS;
	}
}
