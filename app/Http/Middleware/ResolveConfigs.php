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
		$config_manager = $this->resolve_configs($request);

		// Store for the lifetime of THIS request
		$request->attributes->set('configs', $config_manager);

		return $next($request);
	}

	public function resolve_configs(Request $request): ConfigManager
	{
		$config = resolve(ConfigManager::class);
		app()->scoped(ConfigManager::class, fn () => $config);

		return app(ConfigManager::class);
	}
}

