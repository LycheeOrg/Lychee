<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;
use App\Models\Statistics;

/**
 * Persist current Photo object into database.
 */
class SaveStatistics implements PhotoPipe
{
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		if (Statistics::where('photo_id', $state->getPhoto()->id)->count() === 0) {
			$stats = Statistics::create([
				'photo_id' => $state->getPhoto()->id,
				'visit_count' => 0,
				'download_count' => 0,
				'favourite_count' => 0,
				'shared_count' => 0,
			]);

			$state->getPhoto()->setRelation('statistics', $stats);
		}

		return $next($state);
	}
}