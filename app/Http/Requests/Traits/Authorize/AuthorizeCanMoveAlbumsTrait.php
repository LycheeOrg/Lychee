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
 * Authorization of `Album::move`.
 *
 * Target: edit. Each source: move grant on its parent (the album is content
 * of its parent), and ownership when it changes owner.
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

		// Aggregate check: a fixed number of queries, whatever the batch size.
		$album_ids = $this->albums->map(fn (Album $album): string => $album->id)->all();
		if (!Gate::check(AlbumPolicy::CAN_MOVE_ALBUMS_ID, [AbstractAlbum::class, $album_ids])) {
			return false;
		}

		return $this->albums->every(fn (Album $album): bool => $this->mayReparentAcrossOwners($album, $this->album));
	}
}
