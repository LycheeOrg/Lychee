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

class ResolveConfigs
{
	public function handle(Request $request, \Closure $next)
	{
		$config = resolve(ConfigManager::class);

		// Nothing to load: not installed yet (no `configs` table, or no
		// database). Leave the `configs` attribute unset. This load is the
		// one every request needs anyway (e.g. SetLocale), so it costs no
		// extra query, unlike a schema check.
		if (count($config->load()) === 0) {
			return $next($request);
		}

		// Compute ONCE
		$config_manager = $this->resolve_configs($config);

		// Store for the lifetime of THIS request
		$request->attributes->set('configs', $config_manager);

		return $next($request);
	}

	public function resolve_configs(ConfigManager $config): ConfigManager
	{
		app()->scoped(ConfigManager::class, fn () => $config);

		$watermarker = new Watermarker();
		app()->scoped(Watermarker::class, fn () => $watermarker);

		return app(ConfigManager::class);
	}
}

