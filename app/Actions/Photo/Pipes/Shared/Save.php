<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;
use App\Events\PhotoSaved;

/**
 * Persist current Photo object into database.
 */
class Save implements PhotoPipe
{
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		$state->getPhoto()->save();
		$state->getPhoto()->tags()->sync($state->getTags()->pluck('id')->all());

		// Dispatch event for album stats recomputation
		PhotoSaved::dispatch($state->getPhoto());

		return $next($state);
	}
}