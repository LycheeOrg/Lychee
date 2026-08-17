<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Enum\StorageDiskType;
use App\Models\JobHistory;
use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * DO-055-04: mirrors {@link UploadSizeVariantToS3Job} for {@link Track}.
 */
class UploadTrackToS3Job implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	public function __construct(
		protected Track $track,
		int $owner_id,
	) {
		$this->track = $track;

		$this->history = new JobHistory();
		$this->history->owner_id = $owner_id;
		$this->history->job = Str::limit(sprintf('Upload track to S3: %s.', $this->track->file_name), 200);
		$this->history->status = JobStatus::READY;
		$this->history->save();
	}

	public function handle(): void
	{
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$read_stream = Storage::disk(StorageDiskType::LOCAL->value)->readStream($this->track->file_name);
		Storage::disk(StorageDiskType::S3->value)->writeStream($this->track->file_name, $read_stream);
		Storage::disk(StorageDiskType::LOCAL->value)->delete($this->track->file_name);

		$this->track->disk = StorageDiskType::S3;
		$this->track->save();

		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();
	}

	public function failed(\Throwable $th): void
	{
		$this->history->status = JobStatus::FAILURE;
		$this->history->save();

		if ($th->getCode() === 999) {
			$this->release();
		} else {
			Log::channel('jobs')->error(__LINE__ . ':' . __FILE__ . ' Upload failed for ' . $this->track->file_name);
			Log::channel('jobs')->error(__LINE__ . ':' . __FILE__ . ' ' . $th->getMessage(), $th->getTrace());
		}
	}
}
