<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\InvalidPropertyException;

/**
 * Load metadata from the file.
 */
class MayLoadFileMetadata extends LoadFileMetadata implements InitPipe
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidPropertyException
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		if ($state->import_mode->shall_resync_metadata) {
			// Load the metadata from the file
			return parent::handle($state, $next);
		}

		return $next($state);
	}
}

