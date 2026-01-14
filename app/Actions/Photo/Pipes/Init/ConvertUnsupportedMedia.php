<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Actions\Photo\Convert\PhotoConverterFactory;
use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\CannotConvertMediaFileException;

class ConvertUnsupportedMedia implements InitPipe
{
	/**
	 * Tries to convert the file to a supported format.
	 *
	 * @throws CannotConvertMediaFileException
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$ext = ltrim($state->source_file->getOriginalExtension(), '.');

		$factory = new PhotoConverterFactory();
		$converter = $factory->make($ext);
		if ($converter === null) {
			return $next($state);
		}

		$state->source_file = $converter->handle($state->source_file);

		return $next($state);
	}
}