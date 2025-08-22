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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WatermkarkerJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	protected SizeVariant $variant;

	public function __construct(SizeVariant $variant)
	{
		$this->variant = $variant;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = Auth::user()->id;
		$this->history->job = Str::limit(sprintf('Watermark sizeVariant: %s.', $this->variant->short_path), 200);
		$this->history->status = JobStatus::READY;
		$this->history->save();
	}

	public function handle(): void
	{
		if ($this->variant->short_path_watermarked !== null && $this->variant->short_path_watermarked !== '') {
			// Already watermarked, nothing to do.
			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();

			return;
		}

		$watermarker = new Watermarker();
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$watermarker->do($this->variant);

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
			Log::error(__LINE__ . ':' . __FILE__ . ' Watermark failed for ' . $this->variant->short_path);
			Log::error(__LINE__ . ':' . __FILE__ . ' ' . $th->getMessage(), $th->getTrace());
		}
	}
}
