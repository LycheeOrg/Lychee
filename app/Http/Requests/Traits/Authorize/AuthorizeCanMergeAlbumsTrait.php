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

		// Aggregate checks (Q-072-12): a fixed number of queries, whatever the batch size.
		$album_ids = $this->albums->map(fn (Album $album): string => $album->id)->all();
		if (!Gate::check(AlbumPolicy::CAN_MOVE_CONTENT_ID, [AbstractAlbum::class, $album_ids])) {
			return false;
		}
		if (!Gate::check(AlbumPolicy::CAN_DELETE_ID, [AbstractAlbum::class, $album_ids])) {
			return false;
		}

		return $this->albums->every(fn (Album $album): bool => $this->mayReparentAcrossOwners($album, $this->album));
	}
}
