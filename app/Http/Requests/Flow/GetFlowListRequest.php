<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Flow;

use App\Http\Requests\AbstractEmptyRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Request for `GET /api/v3/Flow` (Feature 068, FR-068-01).
 *
 * Mirrors {@see \App\Http\Requests\Flow\FlowRequest}'s authorization exactly
 * (`flow_enabled`, plus `flow_public` for guests), additionally gated by the
 * `features.struct-of-array` flag, mirroring every other v3 SoA request
 * (e.g. {@see \App\Http\Requests\Photo\GetPhotoRatiosRequest}).
 */
class GetFlowListRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		if (config('features.struct-of-array') !== true) {
			return false;
		}

		$flow_enabled = $this->configs()->getValueAsBool('flow_enabled');
		$flow_public = $this->configs()->getValueAsBool('flow_public');

		if (Auth::check()) {
			return $flow_enabled;
		}

		return $flow_enabled && $flow_public;
	}
}
