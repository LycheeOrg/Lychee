<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Sharing;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\AbstractEmptyRequest;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Represents a request for the list of all the access permissions controllable by a user.
 *
 * Only the owner of the album (or the admin) can set the shares.
 */
class ListAllSharingRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, null]);
	}
}