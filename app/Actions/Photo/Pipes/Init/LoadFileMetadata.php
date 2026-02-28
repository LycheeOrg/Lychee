<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
		if ($state->exif_info !== null) {
			// Metadata already loaded
			return $next($state);
		}

		// When a RAW source is available (e.g. CR3, NEF, HEIC) prefer it for
		// metadata extraction because the converted JPEG may lose EXIF data.
		$metadata_file = $state->raw_source_file ?? $state->source_file;

		$state->exif_info = Extractor::createFromFile($metadata_file, $state->file_last_modified_time);

		// Use basename of the original upload for the title, not the converted file
		if (
			$state->exif_info->title === null ||
			$state->exif_info->title === ''
		) {
			$title_source = $state->raw_source_file ?? $state->source_file;
			$state->exif_info->title = substr($title_source->getOriginalBasename(), 0, 98);
		}

		return $next($state);
	}
}

