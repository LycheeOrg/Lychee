<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Set the timestamps of the creation and updated_at time.
 */
class FixTimeStamps extends AbstractStandalonePipe
{
	protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		$state->photo->updateTimestamps();

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.fix_timestamps';
	}
}
