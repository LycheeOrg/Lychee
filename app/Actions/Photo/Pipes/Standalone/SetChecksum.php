<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\DTO\PhotoCreate\StandaloneDTO;
use App\Image\StreamStat;

class SetChecksum extends AbstractStandalonePipe
{
	protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		/** @var StreamStat $stat */
		$stat = $state->stream_stat;

		// The original and final checksum may differ, if the photo has
		// been rotated by `PlacePhoto::putSourceIntoFinalDestination()` while being
		// moved into final position.
		$state->photo->checksum = $stat->checksum;

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.set_checksum';
	}
}
