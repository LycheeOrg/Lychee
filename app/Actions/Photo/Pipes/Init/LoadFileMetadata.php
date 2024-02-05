<?php

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Exceptions\InvalidPropertyException;
use App\Metadata\Extractor;

/**
 * Assert wether we support said file.
 */
class LoadFileMetadata implements PhotoCreatePipe
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidPropertyException
	 */
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
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

