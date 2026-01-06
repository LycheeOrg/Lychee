<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Contracts\Import\ImportPipe;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\Jobs\ImportImageJob;

class ExecuteBatch implements ImportPipe
{
	use HasReporterTrait;

	protected ImportDTO $state;

	/**
	 * Import photos from the import state.
	 *
	 * @param ImportDTO                             $state
	 * @param \Closure(ImportDTO $state): ImportDTO $next
	 *
	 * @return ImportDTO
	 */
	public function handle(ImportDTO $state, \Closure $next): ImportDTO
	{
		if (!$state->should_execute_jobs) {
			return $next($state);
		}

		$total = count($state->job_bus);
		$this->report(ImportEventReport::createNotice('imported', null, 'Processing ' . $total . ' photos...'));
		foreach ($state->job_bus as $idx => $job) {
			try {
				$progress = (int) (($idx + 1) * 100 / $total);
				$path = ($job instanceof ImportImageJob) ? $job->file_path : get_class($job);
				$this->report(ImportEventReport::createDebug('imported', $path, 'Processing... ' . $progress . '%'));
				dispatch($job);
				// @codeCoverageIgnoreStart
			} catch (\Throwable $e) {
				$path = ($job instanceof ImportImageJob) ? $job->file_path : get_class($job);
				$this->report(ImportEventReport::createFromException($e, $path));
			}
			// @codeCoverageIgnoreEnd
		}

		return $next($state);
	}
}