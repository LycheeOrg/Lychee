<?php

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Models\JobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Safe\rmdir;

class CleanUpExtraction implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;

	public string $folderPath;
	public int $userId;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		string $folderPath,
	) {
		$this->folderPath = $folderPath;
		$this->userId = Auth::user()->id;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $this->userId;
		$this->history->job = Str::limit('Removing ' . basename($this->folderPath), 200);
		$this->history->status = JobStatus::READY;

		$this->history->save();
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		// $this->history->status = JobStatus::STARTED;
		// $this->history->save();

		rmdir($this->folderPath);

		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();
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
