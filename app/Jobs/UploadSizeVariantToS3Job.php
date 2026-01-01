<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadSizeVariantToS3Job implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	public function __construct(
		protected SizeVariant $variant,
		int $owner_id,
	) {
		$this->variant = $variant;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $owner_id;
		$this->history->job = Str::limit(sprintf('Upload sizeVariant to S3: %s.', $this->variant->short_path), 200);
		$this->history->status = JobStatus::READY;
		$this->history->save();
	}

	public function handle(): void
	{
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$this->handlePath($this->variant->short_path);
		$this->handlePath($this->variant->short_path_watermarked);

		$this->variant->storage_disk = StorageDiskType::S3;
		$this->variant->save();

		$this->handleVideoPartner();

		// Once the job has finished, set history status to 1.
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
			Log::error(__LINE__ . ':' . __FILE__ . ' Upload failed for ' . $this->variant->short_path);
			Log::error(__LINE__ . ':' . __FILE__ . ' ' . $th->getMessage(), $th->getTrace());
		}
	}

	/**
	 * Move the file from local storage to S3.
	 */
	private function handlePath(?string $path): void
	{
		if ($path === null || $path === '') {
			return;
		}

		$read_stream = Storage::disk(StorageDiskType::LOCAL->value)->readStream($path);
		Storage::disk(StorageDiskType::S3->value)->writeStream($path, $read_stream);
		Storage::disk(StorageDiskType::LOCAL->value)->delete($path);
	}

	/**
	 * If we have a live partner, then we also upload it.
	 */
	private function handleVideoPartner(): void
	{
		if ($this->variant->type !== SizeVariantType::ORIGINAL) {
			return;
		}

		$photo = Photo::query()->where('id', '=', $this->variant->photo_id)->first();

		if ($photo->live_photo_short_path === null) {
			return;
		}

		Storage::disk(StorageDiskType::S3->value)->writeStream(
			$photo->live_photo_short_path,
			Storage::disk(StorageDiskType::LOCAL->value)->readStream($photo->live_photo_short_path)
		);

		Storage::disk(StorageDiskType::LOCAL->value)->delete($photo->live_photo_short_path);
	}
}
