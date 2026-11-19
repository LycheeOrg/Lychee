<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Duplicate;

use App\Contracts\PhotoCreate\DuplicatePipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\Exceptions\PhotoRejectedException;
use App\Repositories\ConfigManager;

class ThrowUnownedDuplicate implements DuplicatePipe
{
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO
	{
		$config_manager = resolve(ConfigManager::class);
		$skip_duplicates_not_owned = $config_manager->getValueAsBool('skip_duplicates_not_owned');
		if ($skip_duplicates_not_owned && $state->photo->owner_id !== $state->intended_owner_id) {
			throw new PhotoRejectedException();
		}

		return $next($state);
	}
}
