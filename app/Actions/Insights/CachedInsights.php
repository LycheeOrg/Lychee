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
 *
 * The scope's years list does not depend on the period: it is kept apart
 * under (scope, revision), stored by every computation and handed to the
 * next one, so a Year or Range computation skips its years pass when the
 * list is known (Feature 108, FR-108-03).
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
		$revision = sha1($this->revision->of($scope));
		$key = implode(':', ['insights', $scope->cacheKey(), $period->cacheKey(), $revision]);
		$years_key = implode(':', ['insights-years', $scope->cacheKey(), $revision]);

		return Cache::remember($key, self::TTL_SECONDS, function () use ($scope, $period, $years_key): InsightsResource {
			$insights = $this->compute->do($scope, $period, Cache::get($years_key));
			Cache::put($years_key, $insights->years, self::TTL_SECONDS);

			return $insights;
		});
	}
}
