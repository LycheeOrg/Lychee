<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;

/**
 * Set fileLastModifiedTime if null.
 */
class FetchLastModifiedTime implements InitPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		if ($state->fileLastModifiedTime === null) {
			$state->fileLastModifiedTime ??= $state->sourceFile->lastModified();
		}

		return $next($state);
	}
}

