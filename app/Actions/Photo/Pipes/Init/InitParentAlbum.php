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
		if ($state->album === null) {
			$state->parameters->album = null;
		} elseif ($state->album instanceof Album) {
			$state->parameters->album = $state->album;
		} elseif ($state->album instanceof BaseSmartAlbum) {
			$state->parameters->album = null;
			if ($state->album instanceof StarredAlbum) {
				$state->parameters->is_starred = true;
			}
		} else {
			throw new InvalidPropertyException('The given parent album does not support uploading');
		}

		return $next($state);
	}
}