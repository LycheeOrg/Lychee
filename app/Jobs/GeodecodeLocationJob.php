<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Metadata\Extractor;
use App\Metadata\Geodecoder;
use App\Models\JobHistory;
use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Asynchronously reverse-geocodes the GPS coordinates of a photo and
 * stores the resolved location string on the photo record.
 */
class GeodecodeLocationJob implements ShouldQueue
{
	use HasFailedTrait;
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;
	public string $photo_id;

	/**
	 * Create a new job instance.
	 */
	public function __construct(Photo $photo)
	{
		$this->photo_id = $photo->id;

		$this->history = new JobHistory();
		$this->history->owner_id = $photo->owner_id;
		$this->history->job = Str::limit(sprintf('Geodecode location for %s [%s].', $photo->title, $this->photo_id), 200);
		$this->history->status = JobStatus::READY;

		$this->history->save();
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		Log::channel('jobs')->info("Starting geodecode location job for photo ID {$this->photo_id}.");
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$photo = Photo::findOrFail($this->photo_id);

		if ($photo->latitude === null || $photo->longitude === null) {
			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();

			return;
		}

		$cached_provider = Geodecoder::getGeocoderProvider();
		$location = Geodecoder::decodeLocation_core($photo->latitude, $photo->longitude, $cached_provider);

		if ($location !== null) {
			$location = substr($location, 0, Extractor::MAX_LOCATION_STRING_LENGTH);
		}

		$photo->location = $location;
		$photo->save();

		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();
	}
}
