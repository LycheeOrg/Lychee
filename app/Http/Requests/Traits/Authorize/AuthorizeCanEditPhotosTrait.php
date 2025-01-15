<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits\Authorize;

use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Determines if the user is authorized to modify the designated photos.
 */
trait AuthorizeCanEditPhotosTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_EDIT, $photo)) {
				return false;
			}
		}

		return true;
	}
}