<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Feature_v2\MoveGrant;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\User;

/**
 * Move-grant fixture on top of {@link \Tests\Feature_v2\Base\BaseApiWithDataTest}.
 *
 * Victim V = userMayUpload1 (owns album1 = VA, its unshared child subAlbum1 = VC,
 * photo1 = P). Attacker A = a fresh `may_upload` user owning attacker_album (AA).
 * A has no permission on V's albums until a test calls {@link grant()}.
 */
trait MoveGrantFixture
{
	protected User $attacker;
	protected Album $attacker_album;

	protected function createMoveGrantFixture(): void
	{
		$this->attacker = User::factory()->may_upload()->create();
		$this->attacker_album = Album::factory()->as_root()->owned_by($this->attacker)->create();
	}

	/**
	 * Share $album with the attacker with exactly the listed grants, replacing any previous share.
	 *
	 * @param string[] $grants subset of edit, move, delete, upload, download, full
	 */
	protected function grant(Album $album, array $grants): AccessPermission
	{
		// Replace any previous share of $album with the attacker (one row per user and album).
		AccessPermission::query()->where('base_album_id', '=', $album->id)->where('user_id', '=', $this->attacker->id)->delete();

		return AccessPermission::factory()->for_user($this->attacker)->for_album($album)->visible()->create([
			'grants_edit' => in_array('edit', $grants, true),
			'grants_move' => in_array('move', $grants, true),
			'grants_delete' => in_array('delete', $grants, true),
			'grants_upload' => in_array('upload', $grants, true),
			'grants_download' => in_array('download', $grants, true),
			'grants_full_photo_access' => in_array('full', $grants, true),
		]);
	}

	protected function ownerOf(Album $album): int
	{
		return Album::query()->findOrFail($album->id)->owner_id;
	}

	protected function parentOf(Album $album): ?string
	{
		return Album::query()->findOrFail($album->id)->parent_id;
	}
}
