<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\InvalidPropertyException;

/**
 * Load metadata from the file.
 */
class MayLoadFileMetadata extends LoadFileMetadata
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidPropertyException
	 */
	protected function execute(InitDTO $state, \Closure $next): InitDTO
	{
		if ($state->import_mode->shall_resync_metadata) {
			// Load the metadata from the file
			return parent::handle($state, $next);
		}

		return $next($state);
	}

	protected function getSpanName(): string
	{
		return 'photo.may_load_file_metadata';
	}
}

