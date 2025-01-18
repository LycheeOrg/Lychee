<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits\Authorize;

use App\Contracts\Models\AbstractAlbum;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Determines if the user is authorized to modify the designated photos and album.
 */
trait AuthorizeCanEditPhotosAlbumTrait
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

		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_EDIT, $photo)) {
				// @codeCoverageIgnoreStart
				return false;
				// @codeCoverageIgnoreEnd
			}
		}

		return true;
	}
}