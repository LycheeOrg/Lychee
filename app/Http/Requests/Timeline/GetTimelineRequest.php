<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Timeline;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class GetTimelineRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Auth::check() && !Configs::getValueAsBool('timeline_photos_public')) {
			return false;
		}

		return Configs::getValueAsBool('timeline_page_enabled');
	}
}