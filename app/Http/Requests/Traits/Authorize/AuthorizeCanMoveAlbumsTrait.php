<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization of `Album::move` (Feature 072, FR-072-13/14/17).
 *
 * Target: edit. Each source: move grant on its parent (the album is content
 * of its parent, Q-072-09), and ownership when it changes owner.
 */
trait AuthorizeCanMoveAlbumsTrait
{
	use GuardsCrossOwnerAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album])) {
			return false;
		}

		foreach ($this->albums as $album) {
			if (!Gate::check(AlbumPolicy::CAN_MOVE_ALBUM, [AbstractAlbum::class, $album])) {
				return false;
			}
			if (!$this->mayReparentAcrossOwners($album, $this->album)) {
				return false;
			}
		}

		return true;
	}
}
