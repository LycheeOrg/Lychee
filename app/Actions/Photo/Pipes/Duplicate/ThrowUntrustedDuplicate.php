<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreate\DuplicatePipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\Exceptions\PhotoSkippedException;

class ThrowUntrustedDuplicate implements DuplicatePipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		if ($state->photo->is_validated) {
			return $next($state);
		}

		throw new PhotoSkippedException('The photo has been skipped, there is already a copy waiting for moderation');
	}
}
