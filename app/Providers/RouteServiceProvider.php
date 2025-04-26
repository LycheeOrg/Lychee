<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * The path to the "home" route for your application.
	 *
	 * This is used by Laravel authentication to redirect users after login.
	 *
	 * @var string
	 */
	public const HOME = '/home';

	/**
	 * If specified, this namespace is automatically applied to your controller routes.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string|null
	 */
	protected $namespace;

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->configureRateLimiting();

		// Note: `web.php` must be registered last, because it contains a
		// "catch all" route and the routes are considered in a "first match"
		// fashion.
		Route::middleware('web-admin')->group(base_path('routes/web-admin-v2.php'));
		Route::middleware('api')->prefix('api/v2')->group(base_path('routes/api_v2.php'));
		Route::middleware('web-install')->group(base_path('routes/web-install.php'));
		Route::middleware('web')->group(base_path('routes/web_v2.php'));
	}

	/**
	 * Configure the rate limiters for the application.
	 *
	 * @return void
	 */
	protected function configureRateLimiting()
	{
		RateLimiter::for('api', function (Request $request) {
			// @codeCoverageIgnoreStart
			return Limit::perMinute(60);
			// @codeCoverageIgnoreEnd
		});
	}
}
