<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\InvalidPropertyException;
use App\Metadata\Extractor;

/**
 * Load metadata from the file.
 */
class LoadFileMetadata implements InitPipe
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidPropertyException
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$state->exif_info = Extractor::createFromFile($state->source_file, $state->file_last_modified_time);

		// Use basename of file if IPTC title missing
		if (
			$state->exif_info->title === null ||
			$state->exif_info->title === ''
		) {
			$state->exif_info->title = substr($state->source_file->getOriginalBasename(), 0, 98);
		}

		return $next($state);
	}
}

