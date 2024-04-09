<?php

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;

/**
 * Assert wether we support said file.
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

