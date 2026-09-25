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
 * Authorization of `Album::merge` (Feature 072, FR-072-15/16/17).
 *
 * Target: edit. Each source is emptied then deleted: move grant on the source
 * (its content leaves it), delete right (grant on its parent), and ownership
 * when its content changes owner.
 */
trait AuthorizeCanMergeAlbumsTrait
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
			if (!Gate::check(AlbumPolicy::CAN_MOVE, [AbstractAlbum::class, $album])) {
				return false;
			}
			if (!Gate::check(AlbumPolicy::CAN_DELETE, [AbstractAlbum::class, $album])) {
				return false;
			}
			if (!$this->mayReparentAcrossOwners($album, $this->album)) {
				return false;
			}
		}

		return true;
	}
}
