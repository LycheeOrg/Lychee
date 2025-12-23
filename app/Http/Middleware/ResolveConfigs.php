<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Repositories\ConfigManager;
use Illuminate\Http\Request;

class ResolveConfigs
{
	public function handle(Request $request, \Closure $next)
	{
		// Compute ONCE
		$config_manager = new ConfigManager();

		// Store for the lifetime of THIS request
		$request->attributes->set('configs', $config_manager);

		return $next($request);
	}
}

