<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Image\Watermarker;
use App\Models\JobHistory;
use App\Models\SizeVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WatermarkerJob implements ShouldQueue, ShouldBeUnique
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	protected SizeVariant $variant;

	// Deduplicate jobs for the same size variant for 1 hour
	public $uniqueFor = 3600;

	/**
	 * Define a unique ID for the job to prevent duplicates in the queue.
	 *
	 * This is required by the ShouldBeUnique interface
	 * We use the size variant ID as the unique identifier
	 * This ensures that only one job per size variant is processed at a time
	 * The unique ID is prefixed with 'watermark:' to avoid collisions with other jobs
	 *
	 * @return string
	 */
	public function uniqueId(): string
	{
		return 'watermark:' . (string) $this->variant->id;
	}

	public function __construct(SizeVariant $variant, int $owner_id)
	{
		$this->variant = $variant;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $owner_id;
		$this->history->job = Str::limit(sprintf('Watermark sizeVariant: %s.', $this->variant->short_path), 200);
		$this->history->status = JobStatus::READY;
		$this->history->save();
	}

	public function handle(): void
	{
		$this->variant->refresh();
		if ($this->variant->is_watermarked) {
			// Already watermarked, nothing to do.
			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();

			return;
		}

		$watermarker = $this->getWatermarker();
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$watermarker->do($this->variant);

		// Assert watermark exists before marking success
		$this->variant->refresh();
		if (!$this->variant->is_watermarked) {
			$this->history->status = JobStatus::FAILURE;
			$this->history->save();

			return;
		}

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
			Log::error(__LINE__ . ':' . __FILE__ . ' Watermark failed for ' . $this->variant->short_path,
				[
					'variant_id' => $this->variant->id,
					'path' => $this->variant->short_path,
					'code' => $th->getCode(),
					'exception' => $th,
				]);
		}
	}

	protected function getWatermarker(): Watermarker
	{
		return resolve(Watermarker::class);
	}
}
