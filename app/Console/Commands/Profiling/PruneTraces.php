<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands\Profiling;

use App\Services\Profiling\TracePruner;
use Illuminate\Console\Command;

/**
 * CLI-053-01: prune the oldest memory-profiler trace pairs beyond the
 * configured retention cap (FR-053-07). Also callable from the schedule
 * (`app/Console/Kernel.php`) and from the admin page's "Prune old traces"
 * button (via {@see \App\Services\Profiling\TracePruner}).
 */
class PruneTraces extends Command
{
	/**
	 * @var string
	 */
	protected $signature = 'lychee:profiler:prune';

	/**
	 * @var string
	 */
	protected $description = 'Delete the oldest memory-profiler trace pairs beyond the configured retention cap.';

	public function handle(TracePruner $pruner): int
	{
		$removed = $pruner->prune();
		$this->line(sprintf('Removed %d trace pair(s) from storage/profiling.', $removed));

		return 0;
	}
}
