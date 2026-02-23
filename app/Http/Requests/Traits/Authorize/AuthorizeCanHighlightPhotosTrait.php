<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits\Authorize;

use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Determines if the user is authorized to modify highlight selection of the designated photos.
 */
trait AuthorizeCanHighlightPhotosTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_HIGHLIGHT, $photo)) {
				return false;
			}
		}

		return true;
	}
}