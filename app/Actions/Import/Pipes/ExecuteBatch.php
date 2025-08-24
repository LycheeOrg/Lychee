<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Contracts\Import\ImportPipe;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;

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
		if (!$state->should_execute_bath) {
			return $next($state);
		}

		$total = count($state->job_bus);
		$this->report(ImportEventReport::createNotice('imported', null, 'Processing ' . $total . ' photos...'));
		foreach ($state->job_bus as $idx => $job) {
			try {
				$progress = (int) (($idx + 1) * 100 / $total);
				$this->report(ImportEventReport::createDebug('imported', $job->file_path, 'Processing... ' . $progress . '%'));
				dispatch($job);
			} catch (\Throwable $e) {
				$this->report(ImportEventReport::createFromException($e, $job->file_path));
			}
		}

		return $next($state);
	}
}