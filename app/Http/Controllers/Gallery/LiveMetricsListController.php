<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Metrics\CleanupMetrics;
use App\Actions\Metrics\QueryLiveMetrics;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Metrics\GetLiveMetricsListRequest;
use App\Http\Resources\V3\LiveMetricsListResource;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves `GET /api/v3/Metrics` (Feature 079): the live-metrics feed as a
 * Struct-of-Arrays, grouped per minute and capped.
 */
class LiveMetricsListController extends Controller
{
	public function index(GetLiveMetricsListRequest $request, QueryLiveMetrics $query_live_metrics, CleanupMetrics $cleanup_metrics): LiveMetricsListResource
	{
		if ($request->configs()->getValueAsBool('live_metrics_enabled') === false) {
			throw new UnauthorizedException('Live metrics are not enabled.');
		}

		/** @var User $user */
		$user = Auth::user();

		$cleanup_metrics->apply();

		return $query_live_metrics->do($user);
	}
}
