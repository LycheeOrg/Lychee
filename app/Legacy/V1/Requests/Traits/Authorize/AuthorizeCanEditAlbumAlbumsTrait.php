<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

trait AuthorizeCanEditAlbumAlbumsTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album])) {
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!Gate::check(AlbumPolicy::CAN_EDIT, $album)) {
				// @codeCoverageIgnoreStart
				return false;
				// @codeCoverageIgnoreEnd
			}
		}

		return true;
	}
}