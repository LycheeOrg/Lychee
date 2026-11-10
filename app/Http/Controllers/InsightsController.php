<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Insights\CachedInsights;
use App\Actions\Insights\ResolveInsightsThumbs;
use App\Http\Requests\Insights\GetInsightsRequest;
use App\Http\Resources\Insights\InsightsResource;
use Illuminate\Routing\Controller;

/**
 * Serves `GET /api/v3/Insights` (Feature 085): library insights for one
 * owner or the whole instance over a period.
 */
class InsightsController extends Controller
{
	public function index(GetInsightsRequest $request, CachedInsights $insights_cache, ResolveInsightsThumbs $thumbs): InsightsResource
	{
		$insights = $insights_cache->get($request->scope(), $request->period());

		$configs = $request->configs();
		$insights->calendar->low = $configs->getValueAsInt('low_number_of_shoots_per_day');
		$insights->calendar->medium = $configs->getValueAsInt('medium_number_of_shoots_per_day');
		$insights->calendar->high = $configs->getValueAsInt('high_number_of_shoots_per_day');

		$thumbs->apply($insights->time_span);

		return $insights;
	}
}
