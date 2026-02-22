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
use App\Models\Album;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\HighlightedAlbum;

/**
 * Init album.
 */
class InitParentAlbum implements InitPipe
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws InvalidPropertyException
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		if ($state->album === null || $state->album instanceof Album) {
			return $next($state);
		}

		if ($state->album instanceof BaseSmartAlbum) {
			if ($state->album instanceof HighlightedAlbum) {
				$state->is_highlighted = true;
			}

			$state->album = null;

			return $next($state);
		}

		throw new InvalidPropertyException('The given parent album does not support uploading');
	}
}