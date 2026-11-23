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
		// A scoped server import never touches another user's photo (FR-115-12).
		// A guest owns no photo: their upload is owned by the album owner, so the owner check
		// alone would link the owner's private photo into the public album (GHSA-9m66-3ccm-jfxg).
		$must_reject = $skip_duplicates_not_owned || $state->shall_scope_to_owner || $state->is_guest_upload;
		$is_unowned = $state->is_guest_upload || $state->photo->owner_id !== $state->intended_owner_id;
		if ($must_reject && $is_unowned) {
			throw new PhotoRejectedException(photo_id: $state->photo->id);
		}

		return $next($state);
	}
}
