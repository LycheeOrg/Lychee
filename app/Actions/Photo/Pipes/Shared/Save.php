<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;
use App\Services\BucketKeys;
use App\Services\MonthDayKey;
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
		// FR-092-02: assigned at every write of `title`.
		$state->getPhoto()->title_date_key = BucketKeys::titleDateKey($state->getPhoto()->title ?? '');
		// FR-106-03: assigned at every write of `taken_at` or `created_at`; a
		// new photo is stamped with the current time by save().
		$state->getPhoto()->month_day_key = MonthDayKey::of($state->getPhoto()->taken_at, $state->getPhoto()->created_at ?? now());

		$state->getPhoto()->save();
		$state->getPhoto()->tags()->sync($state->getTags()->pluck('id')->all());

		return $next($state);
	}
}