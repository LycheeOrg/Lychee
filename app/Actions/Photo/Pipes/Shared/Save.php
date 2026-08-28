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
		// Feature 060 (FR-060-03): explicit sync, no model event. This is the
		// choke-point through which every upload/import pipeline write to
		// `title` passes before being persisted.
		$title_split = TitleSplitter::split($state->getPhoto()->title);
		$state->getPhoto()->title_base = $title_split->base;
		$state->getPhoto()->title_index = $title_split->index;

		$state->getPhoto()->save();
		$state->getPhoto()->tags()->sync($state->getTags()->pluck('id')->all());

		return $next($state);
	}
}