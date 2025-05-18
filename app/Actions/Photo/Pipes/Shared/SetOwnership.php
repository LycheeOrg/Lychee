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
use App\Models\Album;

class SetOwnership implements SharedPipe
{
	public function handle(DuplicateDTO|StandaloneDTO $state, \Closure $next): DuplicateDTO|StandaloneDTO
	{
		$state->photo->owner_id = $state->album instanceof Album ? $state->album->owner_id : $state->intended_owner_id;

		return $next($state);
	}
}