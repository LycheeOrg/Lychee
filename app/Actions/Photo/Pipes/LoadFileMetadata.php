<?php

namespace App\Actions\Photo\Pipes;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Exceptions\InvalidPropertyException;
use App\Metadata\Extractor;
use App\Models\Album;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\StarredAlbum;

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
		$state->strategyParameters->exifInfo = Extractor::createFromFile($state->sourceFile, $state->fileLastModifiedTime);

		// Use basename of file if IPTC title missing
		if (
			$state->strategyParameters->exifInfo->title === null ||
			$state->strategyParameters->exifInfo->title === ''
		) {
			$state->strategyParameters->exifInfo->title = substr($state->sourceFile->getOriginalBasename(), 0, 98);
		}

		if ($state->album === null) {
			$state->strategyParameters->album = null;
		} elseif ($state->album instanceof Album) {
			$state->strategyParameters->album = $state->album;
		} elseif ($state->album instanceof BaseSmartAlbum) {
			$state->strategyParameters->album = null;
			if ($state->album instanceof StarredAlbum) {
				$state->strategyParameters->is_starred = true;
			}
		} else {
			throw new InvalidPropertyException('The given parent album does not support uploading');
		}

		return $next($state);
	}
}

