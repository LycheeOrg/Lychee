<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Actions\Sharing\PurgeAlbumUserThumbs;
use App\Events\UserGroupMembershipChanged;

/**
 * Drops the cached tag/person/smart-album covers of a user whose group
 * membership (or role within a group) just changed.
 *
 * Leaving a group revokes every album access that group granted without
 * deleting a single `access_permissions` row, so none of the permission-side
 * purges are reached - yet `GetPhotoAssetRequest::isComputedAlbumThumb()` would
 * keep honouring the rows those accesses produced. See
 * {@link PurgeAlbumUserThumbs} for why the endpoint relies on this rather than
 * re-checking permissions per request.
 */
class PurgeAlbumUserThumbsOnMembershipChange
{
	public function __construct(
		private PurgeAlbumUserThumbs $purge_thumbs,
	) {
	}

	public function handle(UserGroupMembershipChanged $event): void
	{
		$this->purge_thumbs->forUsers([$event->user_id]);
	}
}
