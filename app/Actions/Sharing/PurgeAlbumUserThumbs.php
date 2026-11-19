<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Sharing;

use App\Constants\PhotoAlbum as PA;
use Illuminate\Support\Facades\DB;

/**
 * Drops cached tag/person/smart-album covers (`album_user_thumbs`) whenever a
 * viewer's access is revoked.
 *
 * `GetPhotoAssetRequest::isComputedAlbumThumb()` accepts a row in this table as
 * proof that the photo legitimately represents the album, bypassing the
 * permission-filtered `photos()` query (see that class, and FR-056-08). That
 * cover exception is what keeps a cached cover resolving during the staleness
 * window before the next {@link \App\Jobs\RecomputeAlbumUserThumbsJob} run —
 * but it also means a row which outlives the access that produced it keeps
 * serving the photo's bytes to a viewer who may no longer see it. The endpoint
 * deliberately does not re-check permissions per request (it is hit once per
 * rendered thumbnail), so **every** revocation path must purge here instead.
 *
 * Current call sites:
 *  - {@link \App\Http\Controllers\Gallery\SharingController::delete()} — a
 *    permission was revoked on an album.
 *  - {@link \App\Actions\Sharing\Propagate::overwrite()} — descendants' own
 *    permissions are wiped and replaced by the ancestor's.
 *  - {@link \App\Listeners\PurgeAlbumUserThumbsOnMembershipChange} — a user
 *    joined/left a group, or their role in it changed.
 *  - {@link \App\Http\Controllers\Admin\UserGroupsController::delete()} — a
 *    whole group, and with it every access it granted, is gone.
 *
 * Already covered elsewhere, no call needed: photo deletion (FK
 * `photo_id` cascades), user deletion ({@link \App\Models\User::delete()}),
 * album deletion ({@link \App\Actions\Album\Delete}), and photo membership
 * changes ({@link \App\Listeners\RecomputeAlbumUserThumbsOnPhotoChange}, whose
 * job recomputes each cached viewer through a permission-filtered query).
 *
 * Purging is deliberately coarse: a missing row costs one lazy recomputation on
 * the viewer's next read (through the permission-filtered live query in
 * {@link \App\Models\Extensions\CachesAlbumUserThumb}), whereas a surviving row
 * is an access leak.
 */
final class PurgeAlbumUserThumbs
{
	/**
	 * Drop every viewer's cached cover which points at a photo of one of
	 * `$base_album_ids`.
	 *
	 * Deliberately not narrowed to the user/group whose permission changed: a
	 * row is keyed by the viewer who materialised it ({@link \Illuminate\Support\Facades\Auth::id()}),
	 * never by the permission their access came from, so an authenticated
	 * viewer who reached the album through its *public* permission stores the
	 * row under their own `user_id` just like everyone else.
	 *
	 * @param array<int,string>|\Illuminate\Support\Collection<int,string> $base_album_ids
	 */
	public function forBaseAlbums(array|\Illuminate\Support\Collection $base_album_ids): void
	{
		$ids = $base_album_ids instanceof \Illuminate\Support\Collection ? $base_album_ids->all() : $base_album_ids;
		if (count($ids) === 0) {
			return;
		}

		DB::table('album_user_thumbs')
			->whereIn('photo_id',
				DB::table(PA::PHOTO_ALBUM)->select(PA::PHOTO_ID)->whereIn(PA::ALBUM_ID, $ids)
			)
			->delete();
	}

	/**
	 * Drop every cached cover belonging to the given viewers.
	 *
	 * Used when what changed is the *viewer's* side of the relation (group
	 * membership), where the set of albums they lost is not readily available:
	 * the group's permissions may have been propagated across whole subtrees,
	 * so enumerating them is both costlier and easier to get wrong than
	 * dropping that viewer's handful of cached covers outright.
	 *
	 * @param array<int,int>|\Illuminate\Support\Collection<int,int> $user_ids
	 */
	public function forUsers(array|\Illuminate\Support\Collection $user_ids): void
	{
		$ids = $user_ids instanceof \Illuminate\Support\Collection ? $user_ids->all() : $user_ids;
		if (count($ids) === 0) {
			return;
		}

		DB::table('album_user_thumbs')->whereIn('user_id', $ids)->delete();
	}
}
