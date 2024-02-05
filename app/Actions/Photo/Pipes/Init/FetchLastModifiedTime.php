<?php

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

/**
 * Assert wether we support said file.
 */
class FetchLastModifiedTime implements PhotoCreatePipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		if ($state->fileLastModifiedTime === null) {
			$state->fileLastModifiedTime ??= $state->sourceFile->lastModified();
		}

		return $next($state);
	}
}

