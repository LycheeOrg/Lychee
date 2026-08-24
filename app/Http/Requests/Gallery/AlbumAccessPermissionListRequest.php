<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Gallery;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\AbstractEmptyRequest;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Request for `GET /api/v3/Albums::accessPermissions`.
 *
 * Same authorization as {@see \App\Http\Requests\Sharing\ListAllSharingRequest}:
 * only a user who owns at least one album (or an admin) may list access
 * permissions.
 */
class AlbumAccessPermissionListRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, null]);
	}
}
