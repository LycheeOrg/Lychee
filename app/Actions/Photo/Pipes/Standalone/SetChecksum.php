<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;

class SetChecksum implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// The original and final checksum may differ, if the photo has
		// been rotated by `PlacePhoto::putSourceIntoFinalDestination()` while being
		// moved into final position.
		$state->photo->checksum = $state->streamStat->checksum;

		return $next($state);
	}
}
