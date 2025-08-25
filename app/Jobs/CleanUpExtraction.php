<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Models\JobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Safe\Exceptions\FilesystemException;
use function Safe\rmdir;
use function Safe\unlink;

class CleanUpExtraction implements ShouldQueue
{
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
			$this->remove_dir($this->folder_path);
			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();

			return;
		}

		$this->history->status = JobStatus::FAILURE;
		$this->history->save();
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

	/**
	 * Actually remove the directory recursively.
	 *
	 * @param string $dir
	 *
	 * @return void
	 *
	 * @throws FilesystemException
	 */
	private function remove_dir(string $dir): void
	{
		$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $file) {
			if ($file->isDir()) {
				rmdir($file->getPathname());
			} else {
				unlink($file->getPathname());
			}
		}
		rmdir($dir);
	}

	/**
	 * Catch failures.
	 *
	 * @param \Throwable $th
	 *
	 * @return void
	 */
	public function failed(\Throwable $th): void
	{
		$this->history->status = JobStatus::FAILURE;
		$this->history->save();

		if ($th->getCode() === 999) {
			$this->release();
		} else {
			Log::error(__LINE__ . ':' . __FILE__ . ' ' . $th->getMessage(), $th->getTrace());
		}
	}
}
