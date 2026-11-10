<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreate\DuplicatePipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\Services\MonthDayKey;
use Illuminate\Support\Facades\Log;

class SaveIfDirty implements DuplicatePipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		// FR-106-03: HydrateMetadata may have rewritten `taken_at`.
		$state->photo->month_day_key = MonthDayKey::of($state->photo->taken_at, $state->photo->created_at);

		// Adopt settings of duplicated photo acc. to target album
		if ($state->photo->isDirty()) {
			Log::notice(__METHOD__ . ':' . __LINE__ . ' Updating metadata of existing photo.');
			$state->photo->save();
			$state->setHasBeenResync(true);
		} else {
			$state->setHasBeenResync(false);
		}

		return $next($state);
	}
}
