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

class PruneEmptyNodes implements ImportPipe
{
	use HasReporterTrait;

	/**
	 * Remove empty nodes from the import state.
	 *
	 * @param ImportDTO                             $state
	 * @param \Closure(ImportDTO $state): ImportDTO $next
	 *
	 * @return ImportDTO
	 */
	public function handle(ImportDTO $state, \Closure $next): ImportDTO
	{
		$this->report(ImportEventReport::createWarning('prune', null, 'Pruning empty folders...'));
		$state->root_folder->pruneEmptyNodes();

		return $next($state);
	}
}