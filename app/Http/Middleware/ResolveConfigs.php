<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Image\Watermarker;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ResolveConfigs
{
	public function handle(Request $request, \Closure $next)
	{
		try {
			if (!Schema::hasTable('configs')) {
				return $next($request);
			}
			// @codeCoverageIgnoreStart
		} catch (\Throwable) {
			return $next($request);
		}

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

		$watermarker = new Watermarker();
		app()->scoped(Watermarker::class, fn () => $watermarker);

		return app(ConfigManager::class);
	}
}

