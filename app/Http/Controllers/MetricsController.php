<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Metrics\CleanupMetrics;
use App\Actions\Metrics\GetMetrics;
use App\Events\Metrics\PhotoFavourite;
use App\Events\Metrics\PhotoVisit;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Metrics\MetricsRequest;
use App\Http\Requests\Metrics\PhotoMetricsRequest;
use App\Http\Resources\Models\LiveMetricsResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * This is a Metrics Controller.
 * Most of the call here do not return anything.
 */
class MetricsController extends Controller
{
	public function get(MetricsRequest $request, GetMetrics $get_metrics, CleanupMetrics $cleanup_metrics): Collection
	{
		if (Configs::getValueAsBool('live_metrics_enabled') === false) {
			throw new UnauthorizedException("Live metrics are not enabled.");
		}

		// First clean up.
		$cleanup_metrics->do();

		// Then fetch.
		return LiveMetricsResource::collect($get_metrics->get());
	}

	/**
	 * This method is called when a photo is visited.
	 */
	public function photo(PhotoMetricsRequest $request): void
	{
		PhotoVisit::dispatchIf(self::shouldMeasure(), $request->visitorId(), $request->photoIds()[0]);

		return;
	}

	/**
	 * This method is called when a photo is marked as favourited.
	 *
	 * Note that it is impossible to know if a photo has been removed from favourites.
	 * This is because the data is stored client-side, as a result, we do not know if
	 * the user is e.g. in incognito mode...
	 */
	public function favourite(PhotoMetricsRequest $request): void
	{
		PhotoFavourite::dispatchIf(self::shouldMeasure(), $request->visitorId(), $request->photoIds()[0]);

		return;
	}

	/**
	 * Determine whether we should apply measurements or not.
	 */
	public static function shouldMeasure(): bool
	{
		if (Configs::getValueAsBool('metrics_enabled') === false) {
			return false;
		}

		if (Auth::guest()) {
			return true;
		}

		if (Auth::user()->may_administrate) {
			return false;
		}

		return Configs::getValueAsBool('metrics_logged_in_users_enabed');
	}
}
