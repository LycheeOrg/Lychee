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
		$state->exifInfo = Extractor::createFromFile($state->sourceFile, $state->fileLastModifiedTime);

		// Use basename of file if IPTC title missing
		if (
			$state->exifInfo->title === null ||
			$state->exifInfo->title === ''
		) {
			$state->exifInfo->title = substr($state->sourceFile->getOriginalBasename(), 0, 98);
		}

		return $next($state);
	}
}

