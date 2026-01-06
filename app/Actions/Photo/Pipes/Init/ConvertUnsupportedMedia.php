<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Actions\Photo\Convert\ImageTypeFactory;
use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\CannotConvertMediaFileException;

class ConvertUnsupportedMedia implements InitPipe
{
	/**
	 * Tries to convert the file to a supported format.
	 *
	 * @throws \Exception
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$ext = ltrim($state->source_file->getOriginalExtension(), '.');

		$factory = new ImageTypeFactory($ext);

		if ($factory->conversionClass === null) {
			return $next($state);
		}

		try {
			$state->source_file = $factory->make()->handle($state->source_file);
		} catch (\Exception $exception) {
			throw new CannotConvertMediaFileException($exception->getMessage(), $exception);
		}

		return $next($state);
	}
}