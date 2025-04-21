<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Metrics;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\LiveMetrics;
use App\Policies\MetricsPolicy;
use Illuminate\Support\Facades\Gate;

class MetricsRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return Gate::check(MetricsPolicy::CAN_SEE_LIVE, LiveMetrics::class);
	}
}
