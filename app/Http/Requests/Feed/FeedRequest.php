<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Feed;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class FeedRequest extends AbstractEmptyRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		$feed_enabled = Configs::getValueAsBool('feed_enabled');
		$feed_public = Configs::getValueAsBool('feed_public');

		// If user is logged in, feed must be enabled
		if (Auth::check()) {
			return $feed_enabled;
		}

		// If user is not logged in, feed must be enabled and public
		return $feed_enabled && $feed_public;
	}
}
