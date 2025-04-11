<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Models\Album;
use App\Models\Statistics;

class SetParentAndOwnership implements SharedPipe
{
	public function handle(DuplicateDTO|StandaloneDTO $state, \Closure $next): DuplicateDTO|StandaloneDTO
	{
		if ($state->album instanceof Album) {
			$state->photo->album_id = $state->album->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->setRelation('album', $state->album);
			$state->photo->owner_id = $state->album->owner_id;
		} else {
			$state->photo->album_id = null;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$state->photo->setRelation('album', null);
			$state->photo->owner_id = $state->intended_owner_id;
		}

		$stats = Statistics::create([
			'photo_id' => $state->photo->id,
			'visit_count' => 0,
			'download_count' => 0,
			'favourite_count' => 0,
			'shared_count' => 0,
		]);

		$state->photo->setRelation('statistics', $stats);

		return $next($state);
	}
}