<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\StandaloneDTO;

class SetStarred implements SharedPipe
{
	public function handle(DuplicateDTO|StandaloneDTO $state, \Closure $next): DuplicateDTO|StandaloneDTO
	{
		// Adopt settings of duplicated photo acc. to target album
		$state->photo->is_starred = $state->is_starred;

		return $next($state);
	}
}