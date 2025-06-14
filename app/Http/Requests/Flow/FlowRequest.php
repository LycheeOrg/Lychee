<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Flow;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class FlowRequest extends AbstractEmptyRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		$flow_enabled = Configs::getValueAsBool('flow_enabled');
		$flow_public = Configs::getValueAsBool('flow_public');

		// If user is logged in, flow must be enabled
		if (Auth::check()) {
			return $flow_enabled;
		}

		// If user is not logged in, flow must be enabled and public
		return $flow_enabled && $flow_public;
	}
}
