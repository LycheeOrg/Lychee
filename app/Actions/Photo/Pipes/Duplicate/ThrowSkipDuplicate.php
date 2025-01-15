<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreate\DuplicatePipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\Exceptions\PhotoResyncedException;
use App\Exceptions\PhotoSkippedException;

class ThrowSkipDuplicate implements DuplicatePipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		if (!$state->shallSkipDuplicates) {
			return $next($state);
		}

		if ($state->hasBeenReSynced ?? false) {
			throw new PhotoResyncedException();
		}
		throw new PhotoSkippedException();
	}
}
