<?php

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Exceptions\InvalidPropertyException;
use App\Models\Album;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\StarredAlbum;

/**
 * Init album.
 */
class InitParentAlbum implements PhotoCreatePipe
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidPropertyException
	 */
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		if ($state->album === null || $state->album instanceof Album) {
			return $next($state);
		}

		if ($state->album instanceof BaseSmartAlbum) {
			if ($state->album instanceof StarredAlbum) {
				$state->is_starred = true;
			}

			$state->album = null;

			return $next($state);
		}

		throw new InvalidPropertyException('The given parent album does not support uploading');
	}
}