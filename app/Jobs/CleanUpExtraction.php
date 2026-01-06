<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Facades\Helpers;
use App\Models\JobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CleanUpExtraction implements ShouldQueue
{
	use HasFailedTrait;
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		public string $folder_path,
		public int $user_id,
	) {
		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->user_id;
		$this->history->job = Str::limit('Removing ' . basename($this->folder_path), 200);
		$this->history->status = JobStatus::READY;

		$this->history->save();
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		// Check if all the sub directories are empty.
		if ($this->is_empty($this->folder_path)) {
			// Only clear the directory if it is empty.
			Helpers::remove_dir($this->folder_path);
			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();

			return;
		}

		// @codeCoverageIgnoreStart
		$this->history->status = JobStatus::FAILURE;
		$this->history->save();
		// @codeCoverageIgnoreEnd
	}

	private function is_empty(string $dir): bool
	{
		$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $file) {
			if (!$file->isDir()) {
				return false;
			}
		}

		return true;
	}
}
