<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Cross-owner guard for album move/merge.
 *
 * Putting an album under an album of another owner hands its whole subtree
 * to that owner (`Album::fixOwnershipOfChildren()`), which is a transfer.
 * Subtree ownership is uniform, so checking the source covers its descendants.
 */
trait GuardsCrossOwnerAlbumsTrait
{
	/**
	 * True when moving $source under $target does not change its owner,
	 * or when the user may transfer $source (i.e. owns it).
	 */
	protected function mayReparentAcrossOwners(Album $source, ?Album $target): bool
	{
		if ($target === null || $source->owner_id === $target->owner_id) {
			return true;
		}

		return Gate::check(AlbumPolicy::CAN_TRANSFER, [AbstractAlbum::class, $source]);
	}
}
