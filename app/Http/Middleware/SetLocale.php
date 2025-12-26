<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Repositories\ConfigManager;
use Illuminate\Http\Request;

class SetLocale
{
	public function handle(Request $request, \Closure $next): mixed
	{
		try {
			$config_manager = app(ConfigManager::class);
			$locale = $config_manager->getValueAsString('lang');
		} catch (\Throwable $e) {
			$locale = config('app.locale');
		}

		app()->setLocale($locale);

		return $next($request);
	}
}
