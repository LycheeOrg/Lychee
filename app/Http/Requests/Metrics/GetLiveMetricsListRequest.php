<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Metrics;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\LiveMetrics;
use App\Policies\MetricsPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Request for `GET /api/v3/Metrics` (Feature 079, FR-079-02).
 *
 * {@see MetricsRequest}'s authorization, plus a logged-in user (the feed is
 * scoped to what the user owns) and the `features.struct-of-array` flag.
 */
class GetLiveMetricsListRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return config('features.struct-of-array') === true &&
			Auth::check() &&
			Gate::check(MetricsPolicy::CAN_SEE_LIVE, LiveMetrics::class);
	}
}
