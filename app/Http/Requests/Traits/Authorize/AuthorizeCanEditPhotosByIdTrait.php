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
 * Determines if the user is authorized to modify the designated photos ids.
 */
trait AuthorizeCanEditPhotosByIdTrait
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photo_ids]);
	}
}