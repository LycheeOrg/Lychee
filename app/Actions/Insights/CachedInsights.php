<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights;

use App\DTO\Insights\InsightsPeriod;
use App\DTO\Insights\InsightsScope;
use App\Http\Resources\Insights\InsightsResource;
use Illuminate\Support\Facades\Cache;

/**
 * Insights cached per (scope, period, library revision) for one hour
 * (Feature 085, NFR-085-06, ADR-085-03). Callers check the scope rights
 * before asking.
 */
class CachedInsights
{
	private const TTL_SECONDS = 3600;

	public function __construct(
		private ComputeInsights $compute,
		private InsightsRevision $revision,
	) {
	}

	public function get(InsightsScope $scope, InsightsPeriod $period): InsightsResource
	{
		$key = implode(':', ['insights', $scope->cacheKey(), $period->cacheKey(), sha1($this->revision->of($scope))]);

		return Cache::remember($key, self::TTL_SECONDS, fn () => $this->compute->do($scope, $period));
	}
}
