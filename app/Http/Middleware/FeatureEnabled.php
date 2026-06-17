<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\FeatureDisabledException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * Class FeatureEnabled.
 *
 * This middleware checks whether a feature flag is enabled via the `features`
 * config file. If the flag resolves to false, a 501 Not Implemented response
 * is returned. Use as `feature:feature_name` in route definitions.
 */
class FeatureEnabled
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request      the incoming request to serve
	 * @param \Closure $next         the next operation to be applied to the request
	 * @param string   $feature_name the key to look up in config('features')
	 *
	 * @throws FeatureDisabledException
	 */
	public function handle(Request $request, \Closure $next, string $feature_name): mixed
	{
		$key = 'features.' . $feature_name;

		if (!Config::has($key)) {
			throw new ConfigurationKeyMissingException(sprintf("Feature key '%s' does not exist in config", $key));
		}

		if (config($key) !== true) {
			throw new FeatureDisabledException($feature_name);
		}

		return $next($request);
	}
}
