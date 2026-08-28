<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;
use App\Services\TitleSplitter;

/**
 * Persist current Photo object into database.
 */
class Save implements PhotoPipe
{
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		$title_split = TitleSplitter::split($state->getPhoto()->title);
		$state->getPhoto()->title_base = $title_split->base;
		$state->getPhoto()->title_index = $title_split->index;

		$state->getPhoto()->save();
		$state->getPhoto()->tags()->sync($state->getTags()->pluck('id')->all());

		return $next($state);
	}
}